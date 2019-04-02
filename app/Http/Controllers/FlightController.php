<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Model\Flight;
use App\Model\Airport;
use Auth;
class FlightController extends Controller
{
    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $airport = DB::table('airports')->get();
      $airplane = DB::table('airplanes')->get();
      $flightClass = DB::table('flight_classes')->get();
      $flight = DB::table('flights')
      ->join('airplanes', 'flights.flight_airplane_id', 'airplanes.id')
      ->join('airports as airport_from', 'flights.flight_airport_from_id', 'airport_from.id')
      ->join('airports as airport_to', 'flights.flight_airport_to_id', 'airport_to.id')
      ->select(
        'flights.*',
        'airplanes.airplane_name',
        'airport_from.airport_code as airport_from_code',
        'airport_from.city_name as city_from',
        'airport_to.airport_code as airport_to_code',
        'airport_to.city_name as city_to'
      )->get();

      return view('index', [
        'flights' => $flight,
        'airplanes' => $airplane,
        'airports' => $airport,
        'flightClasses' => $flightClass
      ]); 
    }

    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $airport = DB::table('airports')->get();
      $airplane = DB::table('airplanes')->get();
      $flightClass = DB::table('flight_classes')->get();
      return view('admin.create-flight', [          
        'airports' => $airport,
        'airplanes' => $airplane,
        'flightClasses' => $flightClass,
      ]);
    }
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function store(Request $request)
    {
      $vali = Validator::make($request->all(), [
        'flight_airport_from' => 'required|different:flight_airport_to',
        'flight_code' => 'required|unique:flights',
        'distance' => 'required',
        'departure-date' => 'required|after_or_equal:today',
        'return-date' => 'after_or_equal:departure-date|nullable',
        'departure-datetime' => 'required|after_or_equal:departure-date',
        'arrival-datetime' => 'required|after_or_equal:departure-datetime',
      ]);

      if ($vali->fails()) {
        return redirect()
        ->back()
        ->withErrors($vali->errors())
        ->withInput();
      } else {
        $input = $request->all();
        $flight = new Flight;
        $flight->flight_class_id = $input['flightClass'];
        $flight->flight_type = $input['flight_type'];
        $flight->flight_code = $input['flight_code'];
        $flight->flight_airplane_id = $input['airplane'];
        $flight->flight_airport_from_id = $input['flight_airport_from'];
        $flight->flight_airport_to_id = $input['flight_airport_to'];
        switch ($input['distance']) {
          case $input['distance'] >= 0 && $input['distance'] <= 100:
          $flight->flight_cost = 500000;
          break;
          case $input['distance'] >= 101 && $input['distance'] <= 200:
          $flight->flight_cost = 1000000;
          break;
          case $input['distance'] >= 201 && $input['distance'] <= 500:
          $flight->flight_cost = 2000000;
          break;
          case $input['distance'] >= 501 && $input['distance'] <= 1000:
          $flight->flight_cost = 3000000;
          break;
          case $input['distance'] >= 1001 && $input['distance'] <= 2000:
          $flight->flight_cost = 6000000;
          break;
          case $input['distance'] >= 2001 && $input['distance'] <= 5000:
          $flight->flight_cost = 20000000;
          break;
          case $input['distance'] >= 5001:
          $flight->flight_cost = 30000000;
          break;
          default:
          break;
        }
        $flight->flight_departure_date = $input['departure-date'];
        $flight->flight_return_date = $input['return-date'];
        $flight->flight_departure_time = $input['departure-datetime'];
        $flight->flight_arrival_time = $input['arrival-datetime'];
        $flight->duration = date('H:i', strtotime($input['arrival-datetime']) - strtotime($input['departure-datetime']));
        $flight->save();
        return redirect()->action('FlightController@create')->with([
          'status' => [
            'created' => "OK"
          ],
          'input' => $input,
        ]);
      }

    }
    public function flightDetail($flight_id){
     $airport = new airport();
     $flight = DB::table('flights')
     ->join('airplanes', 'flights.flight_airplane_id', 'airplanes.id')
     ->join('airports as airport_from', 'flights.flight_airport_from_id', 'airport_from.id')
     ->join('airports as airport_to', 'flights.flight_airport_to_id', 'airport_to.id')
     ->join('flight_classes','flights.flight_class_id', 'flight_classes.id')
     ->select(
      'flights.*',
      'airplanes.airplane_name',
      'airport_from.airport_code as airport_from_code',
      'airport_from.city_name as city_from',
      'airport_from.airport_name as airport_from_name',
      'airport_to.airport_code as airport_to_code',
      'airport_to.city_name as city_to',
      'airport_to.airport_name as airport_to_name',
      'flight_classes.flight_class_name as flight_class'
    );
     $detail = $flight->where('flights.id',$flight_id)->get();
     $airport_from = $airport->where('airports.id',$detail[0]->flight_airport_from_id)->get();
     $airport_to= $airport->where('airports.id',$detail[0]->flight_airport_to_id)->get();
     $flightDetail = $flight->where('flights.id', $flight_id)->get();
     return view('detail_flight', [
      'flight' => $flightDetail[0],
      'airport_from' => $airport_from[0],
      'airport_to' => $airport_to[0]
    ]);
   }
    /**
     * 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {

    }
    /**
     * 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
    /**
     * 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function searchFlight(Request $request)
    {
      $vali = Validator::make($request->all(), [
        'from' => 'required|different:to'
      ]);

      if ($vali->fails()) {
        return redirect()
        ->back()
        ->withErrors($vali->errors())
        ->withInput();
      } else {
        $input = $request->all();

        $flight = DB::table('flights')
        ->join('airplanes', 'flights.flight_airplane_id', 'airplanes.id')
        ->join('airports as airport_from', 'flights.flight_airport_from_id', 'airport_from.id')
        ->join('airports as airport_to', 'flights.flight_airport_to_id', 'airport_to.id')
        ->select(
          'flights.*',
          'airplanes.airplane_name',
          'airport_from.airport_code as airport_from_code',
          'airport_from.city_name as city_from',
          'airport_to.airport_code as airport_to_code',
          'airport_to.city_name as city_to'
        );
        $flight->where('flight_class_id', '=', $input['flight-class']);
        $flight = $flight->where('flight_type', '=', $input['flight_type']);
        $flight->where('flight_airport_from_id', '=', $input['from']);
        $flight->where('flight_airport_to_id', '=', $input['to']);
        if (isset($input['departure-date'])) {
          $flight = $flight->where('flight_departure_date', '=', $input['departure-date']);
        }
        if (isset($input['departure-date'])) {
          $flight = $flight->where('flight_return_date', '=', $input['return-date']);
        }
        $flight = $flight->paginate(5);
        $flight->appends(request()->input())->links();

        $airport = DB::table('airports')->get();
        $airport_from = $airports[$input['from'] - 1];
        $airport_to = $airports[$input['to'] - 1];

        return view('flight-list', [
          'input' => $input,
          'flights' => $flight,
          'airport_from' => $airport_from,
          'airport_to' => $airport_to
        ]);
      }
    }
  }
