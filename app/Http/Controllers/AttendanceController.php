<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->time_in = env('TIME_IN');
        $this->time_out = env('TIME_OUT');
        $this->day_in = env('DAY_IN');
        $this->user = Auth::user();
    }

    public function time_in(Request $request)
    {
        $_data = $request->only('notes', 'type');

        $rule = [
            'notes' => 'string|max:255',
            'type' => ['required', 'integer', Rule::in([1, 2, 3])],
        ];

        if ($request->type != 1) {
            $rule['notes'] = 'required|string|max:255';
        }


        $validate = Validator::make($_data, $rule)->errors()->all();

        if ($validate) {
            return $this->retrunScema($_data, 400, $validate);
        }

        $check_in = $this->check('in');

        if ($check_in === false) {
            return $this->retrunScema($_data, 400, ["Can not in"]);
        }

        $_insert = [
            'type' => $request->type,
            'notes' => $request->notes,
            'user_id' => $this->user->id,
        ];

        $params = $check_in->is_late ? "Late for " : '';
        if ($request->type == 1 && $check_in->is_late) {
            $_insert['notes'] = "$params$check_in->time $this->time_in";
        }

        Attendance::create($_insert);

        $register = [
            'message' => "Time In Success, In $check_in->now ($params$check_in->time $this->time_in)",
        ];
        return $this->retrunScema($register, 201);
    }

    public function time_out()
    {

        $check_out = $this->check('out');

        if ($check_out === false) {
            return $this->retrunScema([], 400, ["Can not out"]);
        }

        Attendance::where('id', $check_out->in_data->id)->update([
            'time_out' => $check_out->now
        ]);

        $register = [
            'message' => "Time Out Success, In $check_out->now ($check_out->time $this->time_out)",
            'check_in_data' => $check_out,
        ];
        return $this->retrunScema($register, 200);
    }

    private function check($type = 'in')
    {
        // Check Is Weekday ?
        $week_day_list = explode(',', $this->day_in);
        if (!in_array(date('l'), $week_day_list)) {
            return false;
        }

        $clock = date('H:i');
        // Check is late < 07:00
        $time_in = Carbon::parse($this->time_in);
        $time_out = Carbon::parse($this->time_out);

        // $now = Carbon::parse("15:10"); // dummy
        $now = Carbon::parse($clock); // dummy
        $is_late = $now->gt($time_in);

        $time_in_diff = $now->diffForHumans($time_in);

        $time_out_diff  = $now->diffForHumans($time_out);

        // DB::enableQueryLog();
        $check  = Attendance::where(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"),
            date('Y-m-d')
        )->where('user_id', $this->user->id)->first();
        // $query = DB::getQueryLog();

        if ($check && $type == 'in') {
            return false;
        }

        if (!$check && $type == 'out') {
            return false;
        }

        if ($type == 'out') {
            if ($check->time_out != null) {
                return false;
            }
        }

        $_return =  [
            'time' => $time_in_diff,
            'now' => date('Y-m-d H:i:s'),
        ];

        if ($type == 'in') {
            $_return['is_late'] = $is_late;
        }

        if ($type == 'out') {
            $_return['time'] = $time_out_diff;
            $_return['in_data'] = $check;
        }

        return (object) $_return;
    }


    public function data()
    {
        $data = Attendance::with('user')->orderBy('id', 'DESC')->paginate();
        return $this->retrunScema($data, 200);
    }
}
