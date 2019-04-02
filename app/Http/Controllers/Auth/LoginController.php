<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
class LoginController extends Controller
{
    use AuthenticatesUsers;
    /**
     * 
     *
     * @var string
     */
    /**
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->isAdmin == 1) {
          return redirect()->route('adminIndex')
          ->with('status', 'Admin login successfully!!!');
      }
      return redirect('/home')->with('status', 'User login successfully!!!');
  }
    /**
     *
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function logout(Request $request) {
      Auth::logout();
      return redirect('/login');
  }
}
