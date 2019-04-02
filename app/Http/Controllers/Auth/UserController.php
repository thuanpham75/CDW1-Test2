<?php
namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
  public function index() {
    $userAd = Auth::user();
    return view('auth.update', [
      'user' => $userAd
    ]);
  }
  public function update(Request $request)
  {
   $userAd = Auth::user();
   $vali = Validator::make($request->all(), [
     'name' => 'required|string|max:255',
     'dob' => 'required',
     'gender' => 'required',
     'phone' => 'required|digits:10',
     'address' => 'required',
     'newPassword' => 'nullable|min:6'
   ]);
   if ($vali->fails()) {
    return redirect()->back()->withInput($request->all())->withErrors($vali->errors());
  } else {
    $userAd->name = $request->name;
    $userAd->dob = $request->dob;
    $userAd->gender = $request->gender;
    $userAd->phone = $request->phone;
    $userAd->address = $request->address;
    if (isset($request->newPassword)) {
     $userAd->password = bcrypt($request->newPassword);
   }
   $userAd->save();
   session()->flash('message', 'Update infomation successfully.');
   return redirect()->back();
 }
}
public function booking($flight_id)
{
  $flight = DB::table('flights')
  ->join('airplanes', 'flights.flight_airplane_id', 'airplanes.id')
  ->join('airports as airport_from', 'flights.flight_airport_from_id', 'airport_from.id')
  ->join('airports as airport_to', 'flights.flight_airport_to_id', 'airport_to.id')
  ->join('flight_classes as class', 'flights.flight_class_id', 'class.id')
  ->select(
    'flights.*',
    'airplanes.airplane_name',
    'airport_from.airport_code as airport_from_code', 
    'airport_from.airport_name as airport_from_name',
    'class.flight_class_name as class_name',
    'airport_from.city_name as city_from',
    'airport_to.airport_code as airport_to_code',
    'airport_to.airport_name as airport_to_name',
    'airport_to.city_name as city_to'
  );
  $userAd_id = Auth::user()->id;
  $userAd_email = Auth::user()->email;
  $userAd_phone = Auth::user()->phone;
  $userAd_name = Auth::user()->name;
  return view('flight-book', [
    'flight' =>  $flight->where('flights.id', '=', $flight_id)->first(),
    'user_id' => $user_id,
    'user_email' => $user_email,
    'user_phone' =>  $user_phone,
    'user_name' =>  $user_name
  ]);
}
}