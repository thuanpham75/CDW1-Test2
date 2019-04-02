<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\User;
use Auth;
use App\Model\Passenger;
use App\Model\BookingList;
use App\Model\Flight;
class BookingController extends Controller
{
    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */ 
    public function create()
    {
        //
    }
    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */ 
    public function editPassenger(Request $request)
    {
        $vali = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
        ]);

        if ($vali->fails()) {
            return redirect()->back()->withInput($request->all())->withErrors($vali->errors());

        } else {                       
            $passenger = Db::table('passenger')->where('id', '=', $request->id)->update([
                'title' => $request->title,
                'pas_first_name' => $request->firstname,
                'pas_last_name' => $request->lastname,
            ]);
            session()->flash('message', 'Update infomation successfully.');
            return redirect()->action('BookingController@MannageTicket', ['userid'=>Auth::user()->id]);
        }
    }

    public function passenger($pasid) {
        $pass =  Db::table('passenger')->where('id', '=', $pasid)->get()->first();
        return view('editpassenger', [
          'passenger' => $pass
      ]);
    }

    /**
     *
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removePassenger($pasid)
    {
        $passenger_del = DB::table('passenger')->where('id', '=', $pasid)->get()->first();        
        $total_pass = DB::table('booking_list')->where('flight_id', '=', $passenger_del->flight_id)->get()->first();
        $total_passenger =  $total_pass->total_passenger-1;

        DB::table('passenger')->where('id', '=', $pasid)->delete();
        DB::table('booking_list')->where('flight_id', '=', $passenger_del->flight_id)->update(['total_passenger'=> $total_passenger]);

        return redirect()->back();
    }
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bookingFlight(Request $request)
    {
        $dataBook = array();
        for ($i=1; $i <= $request->pas; $i++) { 
            $dataBook[] = [ 
                'title'.$i => 'required|string|max:25',
                'pas_first_name'.$i => 'required|string|max:255',
                'pas_last_name'.$i => 'required|string|max:255',];
                
            }

            $vali = Validator::make($request->all(), [
               $dataBook,
               'payment_method' => 'required|string|max:255',
               'card_number' => 'required|string|max:20|min:13',
               'name_card' => 'required|string|max:255',
               'ccv_code' => 'required|string|max:5|min:3',
           ]);

            if ($vali->fails()) {
                return redirect()
                ->back()
                ->withErrors($vali->errors())
                ->withInput();
            }
            else {
                $input = $request->all();
                $booking = new BookingList;           
                $booking->user_id = Auth::user()->id;
                $booking->total_passenger = $input['pas'];
                $booking->total_cost = $input['total_cost'];
                $booking->payment_method = $input['payment_method'];
                $booking->card_number = $input['card_number'];
                $booking->name_card = $input['name_card'];
                $booking->ccv_code = $input['ccv_code'];
                $booking->flight_id = $input['flight_id'];
                for ($i=1; $i <= $request->pas; $i++) { 
                    $title = 'title'.$i;
                    $firstname = 'pas_first_name'.$i;
                    $lastname = 'pas_last_name'.$i;
                    $passenger = new Passenger;
                    $passenger->title = $input[$title];
                    $passenger->pas_first_name = $input[$firstname];
                    $passenger->pas_last_name = $input[$lastname];
                    $passenger->flight_id = $booking->flight_id;
                    $passenger->user_id = Auth::user()->id; 
                    $passenger->save();
                }
                $booking->save();
                return redirect()->action('BookingController@bookingDetail', ['booking'=>$booking, 'passenger'=>$passenger]);
            }
        }
    /**
     * 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bookingDetail($id)
    {
        $booking = DB::table('booking_list')->where('id', '=', $id)->first();
        $flight = DB::table('flights')->where('id', '=', $booking->flight_id)->first();
        $airport_from = DB::table('airports')->where('id', '=',  $flight->flight_airport_from_id)->first();
        $airport_to = DB::table('airports')->where('id', '=',  $flight->flight_airport_to_id)->first();
        $user = Auth::user()->where('id', '=',  $booking->user_id)->first();
        $passenger = DB::table('passenger')->where([
            ['user_id', '=', $booking->user_id],
            ['flight_id', '=', $booking->flight_id]
        ])->get();
        $fare = $passenger->count();
        $cost = $flight->flight_cost * $fare;
        return view('detail_booking', [
            'booking' => $booking,
            'flight' => $flight,
            'airport_from'=> $airport_from,
            'airport_to'=> $airport_to,
            'passenger' => $passenger,
            'user' => $user,
            'cost' => $cost,
            'fare' => $fare
        ]);
    }
    /**
     * 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function MannageTicket($userid)
    { 
        $userAdmin = Auth::user()->id;        
        $user = Db::table('users')->where('id', '=', $userid)->get()->first();
        if ($userid == $userAdmin) {
            $booked = DB::table('booking_list')
            ->join('flights', 'flights.id', 'booking_list.flight_id')  
            ->join('airplanes', 'flights.flight_airplane_id', 'airplanes.id')                          
            ->join('airports as airport_from', 'flights.flight_airport_from_id', 'airport_from.id')
            ->join('airports as airport_to', 'flights.flight_airport_to_id', 'airport_to.id')
            ->select(
                'booking_list.*',
                'flights.*',
                'airplanes.airplane_name',
                'booking_list.id as bookid',
                'airport_from.airport_code as airport_from_code',
                'airport_from.city_name as city_from',
                'airport_to.airport_code as airport_to_code',
                'airport_to.city_name as city_to'
            )->where('user_id', '=', $user->id)->get();
            return view('manage_ticket', [
                'booked' => $booked,
            ]);
        } else {
            return back();
        }      
    }
    /**
     * 
     *
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
     * @param  \Illuminate\Http\Request  $request
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($bookid)
    {
        $book_desc = DB::table('booking_list')->where('id', '=', $bookid)->get()->first();
        DB::table('booking_list')->where('id', '=', $bookid)->delete();
        DB::table('passenger')->where('flight_id', '=', $book_desc->flight_id)->delete();
        return back();
    }
}
