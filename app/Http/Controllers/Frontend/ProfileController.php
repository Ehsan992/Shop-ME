<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Models\User;
use Hash;
use Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $category=DB::table('categories')->orderBy('category_name','ASC')->get();
        $setting=DB::table('settings')->first();
        $orders=DB::table('orders')->where('user_id',Auth::id())->orderBy('id','DESC')->get();

        return view('user.user_dashboard',compact('category','setting','orders'));
    }
    public function setting()
    {
        return view('user.setting');
    }
    public function PasswordChange(Request $request)
    {
       $validated = $request->validate([
           'old_password' => 'required',
           'password' => 'required|min:6|confirmed',
        ]);

        $current_password=Auth::user()->password;  //login user password get


        $oldpass=$request->old_password;  //oldpassword get from input field
        $new_password=$request->password;  // newpassword get for new password
        if (Hash::check($oldpass,$current_password)) {  //checking oldpassword and currentuser password same or not
               $user=User::findorfail(Auth::id());    //current user data get
               $user->password=Hash::make($request->password); //current user password hasing
               $user->save();  //finally save the password
               Auth::logout();  //logout the admin user anmd redirect admin login panel not user login panel
               $notification=array('messege' => 'Your Password Changed!', 'alert-type' => 'success');
               return redirect()->to('/')->with($notification);
        }else{
            $notification=array('messege' => 'Old Password Not Matched!', 'alert-type' => 'error');
            return redirect()->back()->with($notification);
        }
    }


    public function MyOrder()
    {
       $orders=DB::table('orders')->where('user_id',Auth::id())->orderBy('id','DESC')->get();
       return view('user.my_order',compact('orders'));
    }

    //ticekt
    public function ticket()
    {
        $ticket=DB::table('tickets')->where('user_id',Auth::id())->orderBy('id','DESC')->take(10)->get();
        return view('user.ticket',compact('ticket'));
    }

    //new ticket
    public function NewTicket()
    {
       return view('user.new_ticket');
    }

    //store ticket
    public function StoreTicket(Request $request)
    {
        $validated = $request->validate([
           'subject' => 'required',
        ]);

        $data=array();
        $data['subject']=$request->subject;
        $data['service']=$request->service;
        $data['priority']=$request->priority;
        $data['message']=$request->message;
        $data['user_id']=Auth::id();
        $data['status']=0;
        $data['date']=date('Y-m-d');

         if($request->image){
			$manager = new ImageManager(new Driver());
			$photoname = hexdec(uniqid()).'.'.$request->image->getClientOriginalExtension();
			$img = $manager->read($request->image);
			$img = $img->resize(600,350);
			$img->save('files/ticket/'.$photoname);
			$data['image']='files/ticket/'.$photoname;   // files/ticket/plus-point.jpg
		} 


        DB::table('tickets')->insert($data);
        $notification=array('messege' => 'Ticket Inserted!', 'alert-type' => 'success');
        return redirect()->route('open.ticket')->with($notification);
    }

    //__ticket show
    public function ticketShow($id)
    {
        $ticket=DB::table('tickets')->where('id',$id)->first();
        return view('user.show_ticket',compact('ticket'));
    }


    //__reply ticket
    public function ReplyTicket(Request $request)
    {
        $validated = $request->validate([
           'message' => 'required',
        ]);

        $data=array();
        $data['message']=$request->message;
        $data['ticket_id']=$request->ticket_id;
        $data['user_id']=Auth::id();
        $data['reply_date']=date('Y-m-d');

        if($request->image){
			$manager = new ImageManager(new Driver());
			$photoname = hexdec(uniqid()).'.'.$request->image->getClientOriginalExtension();
			$img = $manager->read($request->image);
			$img = $img->resize(600,350);
			$img->save('files/ticket/'.$photoname);
			$data['image']='files/ticket/'.$photoname;   // files/ticket/plus-point.jpg
		}

        
        
        DB::table('replies')->insert($data);

        DB::table('tickets')->where('id',$request->ticket_id)->update(['status'=>0]);

        $notification=array('messege' => 'Replied Done!', 'alert-type' => 'success');
        return redirect()->back()->with($notification);
    }

    //__cuistoomer oder details
    public function ViewOrder($id)
    {
        $order=DB::table('orders')->where('id',$id)->first();
        //$order=Order::findorfail($id);
        $order_details=DB::table('order_details')->where('order_id',$id)->get();

        return view('user.order_details',compact('order','order_details'));
    }

    protected function updateProfile(Request $request)
    {
        if ($request->user_photo) {
            $manager = new ImageManager(new Driver());
            $photoname = hexdec(uniqid()) . '.' . $request->user_photo->getClientOriginalExtension();
            $img = $manager->read($request->user_photo);
            $img = $img->resize(180,180);
            $img->save('files/user/' . $photoname);
            $data['user_photo'] = 'files/user/' . $photoname;
            DB::table('users')->where('id',$request->id)->update($data); 
        }

        $notification=array('messege' => 'Profile updated successfully.', 'alert-type' => 'success');
        return redirect()->back()->with($notification);
    }
}
