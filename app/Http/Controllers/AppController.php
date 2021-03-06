<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Traits\WaitTimeManager;
use App\BranchService;
use App\Queue;
use Session;
use JavaScript;
use DB;

class AppController extends Controller
{
	use WaitTimeManager; 

	public function __construct()
    {
    	// $user = Auth::user();

    	// JavaScript::put([
	    //     'branchId' => $user->branch_id
	    // ]);
    }

    public function test(){
    	//
    }

    public function setBranchId(){
    	$user = Auth::user();

    	JavaScript::put([
	        'branchId' => $user->branch_id
	    ]);
    }

	public function checkAuth(){
		
		return (string)Auth::check();
	}

    public function getSidebarSession() {

		// return Session()->get('sidebar');
		return session('sidebar');
	}

	public function setSidebarSession(Request $request) {

		session(['sidebar' => $request->sidebar]);

		// Session()->put('sidebar', $request->sidebar);

		return $this->getSidebarSession();
		// return $request->session()->all();
	}

	public function getTabSession() {

		return session('tab');
	}

	public function testFunction(){

		 return "test";
	}

	public function setTabSession(Request $request) {

		session(['tab' => $request->tab]);

		return $this->getTabSession();
	}

	public function getAllSession(Request $request){

		return $request->session()->all();
	}

	/** 
	 * Convert wait time in seconds
	 * to a proper string for display purpose.
	 */

	public static function secToString($seconds) {
		$hours = floor($seconds / 3600);
  		$minutes = floor(($seconds / 60) % 60);
  		$seconds = $seconds % 60;

        $stringTime = "";

        if($hours > 0){

            $stringTime = $hours." hr";

            if($minutes > 0){

                $stringTime = $stringTime." ".$minutes." min";
            }
        }
        else if($minutes > 0){
            
            $stringTime = $minutes." min";

            if($seconds > 0){

                $stringTime = $stringTime." ".$seconds." sec";    
            }
        }
        else if($seconds > 0){

            $stringTime = $seconds." sec";
        }

        return $stringTime;
	}

	public function formatTime($datetime){

	    return Carbon::parse($datetime, 'Asia/Kuala_Lumpur')->format('g:i A');    
	}

	public function formatDateTime($datetime){

	    return Carbon::parse($datetime, 'Asia/Kuala_Lumpur')->format('d M Y g:i A');    
	}

	public static function secToTime($seconds) {
		$hours = floor($seconds / 3600);
  		$minutes = floor(($seconds / 60) % 60);
  		$seconds = $seconds % 60;
  
  		return ("$hours,$minutes,$seconds");
	}

	public static function timeToSec($hr, $min, $sec){

		return $sec + ($min * 60) + ($hr * 3600);
	}

	public static function timeToArray($branchServices){
		$wait_time_array = array();

        foreach ($branchServices as $branchService){
            $default_wait_time_string = self::secToTime($branchService->default_wait_time);
            $default_wait_time_exploded = explode(",", $default_wait_time_string);

            $system_wait_time_string = self::secToTime($branchService->system_wait_time);
            $system_wait_time_exploded = explode(",", $system_wait_time_string);
            
            $wait_time_array[$branchService->id]['default_wait_time_hr'] = $default_wait_time_exploded[0];
            $wait_time_array[$branchService->id]['default_wait_time_min'] = $default_wait_time_exploded[1];
            $wait_time_array[$branchService->id]['default_wait_time_sec'] = $default_wait_time_exploded[2];
            $wait_time_array[$branchService->id]['system_wait_time_hr'] = $system_wait_time_exploded[0];
            $wait_time_array[$branchService->id]['system_wait_time_min'] = $system_wait_time_exploded[1];
            $wait_time_array[$branchService->id]['system_wait_time_sec'] = $system_wait_time_exploded[2];
        }

        return $wait_time_array;
	}

	public function generateSysWaitTime(){

		// Calculate average wait time of each branchService
        $branchServices = BranchService::get();
        
        $appController = new AppController;
        $sysWaitTime = 0;
        
        foreach($branchServices as $branchService){

            DB::beginTransaction();

            try {

                $queues = $branchService->inactive_queue;

                $totalWaitTime = $queues->sum('avg_wait_time');
                $noOfQueue = $queues->count();
                $sysWaitTime = $this->calAvgWaitTime($totalWaitTime, $noOfQueue);

                $branchService->system_wait_time = $sysWaitTime;
                $branchService->save();

                DB::commit();

            } catch (\Exception $e) {

                DB::rollback();

                throw $e;
            }

            return redirect()->route('home')->with('success', 'Data refreshed!');
        }
	}

}
