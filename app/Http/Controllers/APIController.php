<?php

namespace App\Http\Controllers;

use App\Comments;
use App\Contacts;
use App\Friends;
use App\LocationMaster;
use App\Post_likes;
use App\Post_media;
use App\TripFriend;
use App\TripMembers;
use App\TripPost;
use App\TripPostTags;
use App\Trips;
use App\User;
use App\UserNotifications;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PhpParser\filesInDir;
use Validator;
use File;

class APIController extends Controller
{

    /**************Rest API Function**************/
    public function sendResponse($result, $message)
    {
        $response = [
            'status' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'status' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
    /**************Rest API Function**************/


    /**************Contacts Master**********************/
    public function create_contact(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'mobile' => 'required',
            'contactname' => 'required',
//            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contacts = new Contacts();
        $contacts->mobile = request('mobile');
        $contacts->contactname = request('contactname');
//        $contacts->contactstatus = request('status');
        $file = $request->file('profile_img');
        if (request('profile_img') != null) {
            $data = request('profile_img');
            list($type, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);
            $image_name = time() . '.png';
            $path = "images/" . $image_name;
            file_put_contents($path, $data);
            $contacts->imageurl = $path;
        }
        $contacts->save();
        $contact = Contacts::find($contacts->id);
        return $this->sendResponse($contact, 'Contacts has been saved');
    }////////////profile_img+


    public function google_fb_login(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'google_fb_id' => 'required',
            'contactname' => 'required',
//            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $fb_contact = Contacts::where(['google_fb_id' => request('google_fb_id')])->first();
        if (isset($fb_contact)) {
            return $this->sendResponse($fb_contact, 'Login through social media');
        } else {
            $contacts = new Contacts();
//            $contacts->mobile = request('mobile');
            $contacts->google_fb_id = request('google_fb_id');
            $contacts->contactname = request('contactname');
            $file = $request->file('profile_img');
            if (request('profile_img') != null) {
                $data = request('profile_img');
                list($type, $data) = explode(';', $data);
                list(, $data) = explode(',', $data);
                $data = base64_decode($data);
                $image_name = time() . '.png';
                $path = "images/" . $image_name;
                file_put_contents($path, $data);
                $contacts->imageurl = $path;
            }
            $contacts->save();
            $fbcontact = Contacts::find($contacts->id);
            return $this->sendResponse($fbcontact, 'Contacts has been saved');
        }

    }////////////profile_img+

    public function updatecontact(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
            'contactname' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contacts = Contacts::find(request('id'));
        if (isset($contacts) > 0) {
            $contacts->contactname = request('contactname');
            $file = $request->file('profile_img');
            if (request('profile_img') != null) {
                $data = request('profile_img');
                list($type, $data) = explode(';', $data);
                list(, $data) = explode(',', $data);
                $data = base64_decode($data);
                $image_name = time() . '.png';
                $path = "images/" . $image_name;
                file_put_contents($path, $data);
                $contacts->imageurl = $path;
            }
            $contacts->save();
            return $this->sendResponse($contacts, 'Contact has been updated');
        } else {
            return $this->sendError('No record available', '');
        }
    }////////////profile_img+

    public function get_all_contacts(Request $request)
    {
        $contact = DB::select("select id, mobile, contactname, contactstatus, imageurl from contacts where isactive = 1");
        if (isset($contact) > 0) {
            return $this->sendResponse($contact, 'Contacts List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function deletecontact(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = Contacts::find(request('id'));
        if (isset($location) > 0) {
            $location->isactive = 0;
            $location->save();
            return $this->sendResponse([], 'Contact has been deleted');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function setprivacy(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
            'privacy' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $privacy = request('privacy');
        $contact = Contacts::find(request('user_id'));
        if (isset($contact)) {
            $contact->privacy = $privacy;
            $contact->save();

            $trip_posts = TripPost::where(['post_by' => $contact->id])->get();
            if (count($trip_posts) > 0) {
                foreach ($trip_posts as $trip_post) {
                    $trip_post->post_privacy = $privacy;
                    $trip_post->save();
                }
            }
            return $this->sendResponse([], 'Privacy has been updated');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function get_contact_by_mobile(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $mobile = request('mobile');
        $friends = DB::select("select id, mobile, contactname, contactstatus,imageurl from contacts where mobile= $mobile");
        if (isset($friends) > 0) {
            return $this->sendResponse($friends, 'Contact List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function resendotp(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $mobile = request('mobile');

        $otp = rand(100000, 999999);
        $contact = Contacts::where(['mobile' => $mobile])->first();
        if (isset($contact))
            $user = ['status' => 'exist', 'otp' => $otp, 'info' => $contact];
        else
            $user = ['status' => 'notexist', 'otp' => $otp];


        file_get_contents("http://63.142.255.148/api/sendmessage.php?usr=retinodes&apikey=1A4428ABD1CB0BD43FB3&sndr=iapptu&ph=$mobile&message=Dear%20user,%20OTP%20to%20login%20into%20trippy%20app%20is%20$otp");
        return $this->sendResponse($user, 'Otp has been send');
    }

    public function searchuser(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'search_name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $s = request('search_name');
        $user = DB::select("SELECT c.id, c.contactname, c.imageurl FROM contacts c WHERE c.isactive = 1 and (c.contactname LIKE '$s%' or c.mobile = '$s')");
        if ($user != null) {
            return $this->sendResponse($user, 'Contacts List');
        } else {
            return $this->sendError('No record available', '');
        }
    }
    /**************Contacts Master**********************/

    /**************Location Master**********************/
    public function create_location(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'ln' => 'required',
            'addr' => 'required',
            'lat' => 'required',
            'lon' => 'required',
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = new LocationMaster();
        $location->loc_name = request('ln');
        $location->address = request('addr');
        $location->lat = request('lat');
        $location->lon = request('lon');
        $location->contactid = request('contactid');
        $location->save();
        return $this->sendResponse($location, 'Location has been saved');
    }

    public function updatelocation(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
            'ln' => 'required',
            'addr' => 'required',
            'lat' => 'required',
            'lon' => 'required',
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = LocationMaster::find(request('id'));
        if (isset($location) > 0) {
            $location->loc_name = request('ln');
            $location->address = request('addr');
            $location->lat = request('lat');
            $location->lon = request('lon');
            $location->contactid = request('contactid');
            $location->save();
            return $this->sendResponse($location, 'Location has been updated');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function deletelocation(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = LocationMaster::find(request('id'));
        if (isset($location) > 0) {
            $location->isdel = 1;
            $location->save();
            return $this->sendResponse([], 'Location has been deleted');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function getlocation_byid(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = LocationMaster::find(request('id'));
        if (isset($location) > 0) {
            return $this->sendResponse($location, 'Location Record');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function getlocation_bycontactid(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = LocationMaster::where(['isdel' => 0, 'contactid' => request('contactid')])->get();
        if (isset($location) > 0) {
            return $this->sendResponse($location, 'Location Record');
        } else {
            return $this->sendError('No record available', '');
        }
    }
    /**************Location Master**********************/


    /**************Trips Master**********************/
    public function setpostprivacy(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'post_id' => 'required',
            'privacy' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $privacy = request('privacy');
        $trip_post = TripPost::find(request('post_id'));
        if (isset($trip_post)) {
            $trip_post->post_privacy = $privacy;
            $trip_post->save();
            return $this->sendResponse([], 'Privacy has been updated');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function create_trips(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'tripname' => 'required',
            'contactid' => 'required',
            'tripdate' => 'required',
            'triptime' => 'required',
            'tripstatus' => 'required',
            'tripnotes' => 'required',
            'locationid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip = new Trips();
        $trip->tripname = request('tripname');
        $trip->contactid = request('contactid');
        $trip->tripdate = request('tripdate');
        $trip->triptime = request('triptime');
        $trip->tripstatus = request('tripstatus');
        $trip->tripnotes = request('tripnotes');
        $trip->locationid = request('locationid');
        $trip->save();

        $friends = explode(",", request('friend_id'));
        if ((request('friend_id') != null)) {
            foreach ($friends as $friend_id) {
                $trip_pt = new TripFriend();
                $trip_pt->trip_id = $trip->id;
                $trip_pt->friend_id = $friend_id;
                $trip_pt->save();
            }
        }

        return $this->sendResponse($trip, 'Trips has been saved');
    }


    public
    function updatetrips(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'trip_id' => 'required',
//            'tripname' => 'required',
//            'contactid' => 'required',
//            'tripdate' => 'required',
//            'triptime' => 'required',
//            'tripstatus' => 'required',
//            'tripnotes' => 'required',
//            'locationid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trips = Trips::find(request('trip_id'));
        if (isset($trips)) {
            $trips->tripname = request('tripname');
            $trips->contactid = request('contactid') == null ? $trips->contactid : request('contactid');
            $trips->tripdate = request('tripdate') == null ? $trips->tripdate : request('tripdate');
            $trips->triptime = request('triptime') == null ? $trips->triptime : request('triptime');
            $trips->tripstatus = request('tripstatus') == null ? $trips->tripstatus : request('tripstatus');
            $trips->tripnotes = request('tripnotes') == null ? $trips->tripnotes : request('tripnotes');
//            $trips->locationid = request('locationid');
            $trips->save();
//            $location = LocationMaster::find($trips->locationid);
//            $location->loc_name = request('loc_name') == null ? $location->loc_name : request('loc_name');
//            $location->address = request('address') == null ? $location->address : request('address');
//            $location->save();

            $tag = TripFriend::where(['trip_id' => $trips->id])->delete();

            $friends = explode(",", request('friend_id'));
            if ((request('friend_id') != null)) {
                foreach ($friends as $friend_id) {
                    $trip_pt = new TripFriend();
                    $trip_pt->trip_id = $trips->id;
                    $trip_pt->friend_id = $friend_id;
                    $trip_pt->save();
                }
            }

            return $this->sendResponse($trips, 'Trips has been updated');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function getalltrips(Request $request)
    {
        $trips = DB::select("select id, tripname,contactid,tripdate,triptime,tripstatus,tripnotes,locationid from trips where isdel=0");
        if (count($trips) > 0) {
            foreach ($trips as $trip) {
                $trip_friends = DB::select("select c.id as fid, c.contactname, c.imageurl from contacts c, trip_friends tf where tf.friend_id = c.id and tf.trip_id = $trip->id");
                $results[] = ['id' => $trip->id, 'tripname' => $trip->tripname, 'contactid' => $trip->contactid, 'tripdate' => $trip->tripdate, 'triptime' => $trip->triptime, 'tripstatus' => $trip->tripstatus, 'tripnotes' => $trip->tripnotes, 'locationid' => $trip->locationid, 'trip_friends' => $trip_friends];
            }
            return $this->sendResponse($results, 'Trips List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function get_my_trip(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contactid = request('contactid');
        $trips = DB::select("select t.id, t.tripname,t.contactid,t.tripdate,t.triptime,t.tripstatus,t.tripnotes, l.address, l.lat,l.lon,l.loc_name from trips t, locations l where t.locationid= l.id and t.isdel=0 and t.contactid=$contactid");

        if (count($trips) > 0) {
            foreach ($trips as $trip) {
                $trip_friends = DB::select("select c.id as fid, c.contactname, c.imageurl from contacts c, trip_friends tf where tf.friend_id = c.id and tf.trip_id = $trip->id");// TripFriend::where(['trip_id' => $trip->id])->get();
                $results[] = ['id' => $trip->id, 'tripname' => $trip->tripname, 'contactid' => $trip->contactid, 'tripdate' => $trip->tripdate, 'triptime' => $trip->triptime, 'tripstatus' => $trip->tripstatus, 'tripnotes' => $trip->tripnotes, 'address' => $trip->address, 'lat' => $trip->lat, 'lon' => $trip->lon, 'loc_name' => $trip->loc_name, 'trip_friends' => $trip_friends];
            }
            return $this->sendResponse($results, 'Trips List');
        } else {
            return $this->sendError('No record available', '');
        }


//        if (count($trips) > 0) {
//            return $this->sendResponse($trips, 'My Trips List');
//        } else {
//            return $this->sendError('No record available', '');
//        }
    }

    public
    function deletetrips(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = Trips::find(request('id'));
        if (isset($location) > 0) {
            $location->isdel = 1;
            $location->save();
            return $this->sendResponse($location, 'Trip has been deleted');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function gettrips_byid(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $location = Trips::find(request('id'));
        if (isset($location) > 0) {
            return $this->sendResponse($location, 'Trip Record');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    /**************Trips Master**********************/


    /**************TripMembers Master**********************/
    public
    function create_tripmembers(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'tripid' => 'required',
            'adminid' => 'required',
            'contactid' => 'required',
            'tripstatus' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $tripm = new TripMembers();
        $tripm->tripid = request('tripid');
        $tripm->adminid = request('adminid');
        $tripm->contactid = request('contactid');
        $tripm->tripstatus = request('tripstatus');
        $tripm->save();
        return $this->sendResponse($tripm, 'Trip Members has been saved');
    }

    public
    function get_tripfriends(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'tripid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $tripid = request('tripid');
        $trips = DB::select("select id, tripid,adminid,contactid,tripstatus from tripmembers where tripid=$tripid");
        if (count($trips) > 0) {
            return $this->sendResponse($trips, 'Trip Friends List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function deletetripmembers(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trips = TripMembers::where(['contactid' => request('contactid')])->first();
        if (isset($trips) > 0) {
            return $this->sendResponse($trips, 'Trip has been deleted');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    /**************TripMembers Master**********************/


    /**************Trip Post Master**********************/
    public
    function share_trip_post(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'post_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $post_id = request('post_id');
        $user_id = request('user_id');
        $trip = TripPost::find($post_id);
        $trip_post = new TripPost();
        $trip_post->description = $trip->description;
        $trip_post->trip_id = $trip->trip_id;
        $trip_post->post_created_by = $trip->post_by;
        $trip_post->post_by = $user_id;
        $trip_post->caption = $trip->caption;
        $trip_post->save();

        $post_medias = Post_media::where(['post_id' => $post_id])->get();
        if (count($post_medias) > 0) {
            foreach ($post_medias as $media) {
                $post_media = new Post_media();
                $post_media->post_id = $trip_post->id;
                $post_media->media_url = $media->media_url;
                $post_media->media_type = $media->media_type;
                $post_media->save();
            }
        }

        $pst = TripPost::find($trip_post->id);
        return $this->sendResponse($pst, 'Trip Post has been shared');
    }

    public
    function create_trip_post(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
//            'title' => 'required',
//            'description' => 'required',
            'post_by' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip_post = new TripPost();
//        $trip_post->title = request('title');
        $trip_post->description = request('description');
        $trip_post->trip_id = request('trip_id');
        $trip_post->post_by = request('post_by');
        if (request('visible_till') == '24')
            $trip_post->visible_till = Carbon::now('Asia/Kolkata')->addHour(24);
        else if (request('visible_till') == '48')
            $trip_post->visible_till = Carbon::now('Asia/Kolkata')->addHour(48); //request('visible_till');
        $trip_post->caption = request('caption');
        $trip_post->created_time = Carbon::now('Asia/Kolkata');
        $trip_post->save();

        $tids = explode(",", request('tagid'));
        if ((request('tagid') != null)) {
            foreach ($tids as $tagid) {
                $trip_pt = new TripPostTags();
                $trip_pt->trip_post_id = $trip_post->id;
                $trip_pt->contactid = $tagid;
                $trip_pt->save();
            }
        }

        if (request('post_img') != null) {
            $array = $request->input('post_img');

            foreach (json_decode($array) as $obj) {
                $post_media = new Post_media();
                $post_media->post_id = $trip_post->id;
                $data = $obj->image;
                $data = base64_decode($data);
                $image_name = str_random(6) . "$obj->type";
                $destinationPath = './post_images/' . $image_name;
                file_put_contents($destinationPath, $data);
                $post_media->media_url = 'post_images/' . $image_name;
                $post_media->media_type = $obj->type == '.png' ? 'img' : 'vd';
                $post_media->save();
            }
        }

        $pst = TripPost::find($trip_post->id);
        return $this->sendResponse($pst, 'Trip Post has been saved');
    }////////////profile_img+

    public
    function updatetrip_post(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'post_id' => 'required',
            'title' => 'required',
//            'description' => 'required',
//            'trip_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip_post = TripPost::find(request('post_id'));
        if (isset($trip_post) > 0) {
            $trip_post->title = request('title');
//            $trip_post->description = request('description');
//            $trip_post->trip_id = request('trip_id');
            $trip_post->save();
            $file = $request->file('post_img');
            if ($request->file('post_img') != null) {
                $post_media = new Post_media();
                $post_media->post_id = $trip_post->id;
                $destination_path = 'post_images/';
                $filename = str_random(6) . '_' . $file->getClientOriginalName();
                $file->move($destination_path, $filename);
                $post_media->media_url = 'post_images/' . $filename;
                $post_media->save();
            }
            return $this->sendResponse($trip_post, 'Trip Post has been updated');
        } else {
            return $this->sendError('No record available', '');
        }
    }////////////profile_img+

    public
    function edit_trip_post(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'post_id' => 'required',
            'visible_till' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip_post = TripPost::find(request('post_id'));
        if (isset($trip_post) > 0) {
            if (request('visible_till') == '24')
                $trip_post->visible_till = Carbon::now('Asia/Kolkata')->addHour(24);
            elseif (request('visible_till') == '48')
                $trip_post->visible_till = Carbon::now('Asia/Kolkata')->addHour(48);
            else
                $trip_post->visible_till = null;
            $trip_post->save();
            return $this->sendResponse($trip_post, 'Trip Post visibility has been set');
        } else {
            return $this->sendError('No record available', '');
        }
    }////////////profile_img+

    public
    function get_Alltrip_posts(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user_id = request('user_id');
        $posts = DB::select("SELECT *, (select cn.contactname from contacts cn where cn.id = pt.post_by) as posted_by, (select cn.imageurl from contacts cn where cn.id = pt.post_by) as u_image, pt.post_by as user_id, (select cn.contactname from contacts cn where cn.id = pt.post_created_by) as post_created_by_name from trip_post pt where pt.is_del = 0 and pt.is_hide = 0 and pt.post_by = $user_id or pt.post_by in (select fr.contactid from friends fr, contacts unn where fr.status='friends' and fr.friendid=$user_id and unn.id=fr.contactid) or pt.post_by in (select f.friendid from friends f, contacts un where f.status='friends' and f.contactid=$user_id and un.id= f.friendid) ORDER BY pt.id DESC");
        $numrows = count($posts);
        $rowsperpage = 10;
        $totalpages = ceil($numrows / $rowsperpage);
        if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
            $currentpage = (int)$_GET['currentpage'];
        } else {
            $currentpage = 1;  // default page number
        }
        if ($currentpage < 1) {
            $currentpage = 1;
        }

        $offset = ($currentpage - 1) * $rowsperpage;
//        $s = "select pt.*, c.contactname as posted_by, c.imageurl as u_image, c.id as user_id, pt.created_time from trip_post pt, contacts c where  pt.post_by = c.id and pt.is_del = 0 and pt.is_hide = 0 and pt.post_by =$user_id or  pt.post_by in (select fr.contactid from friends fr, contacts unn where fr.status='friends' and fr.friendid=$user_id and unn.id=fr.contactid) or pt.post_by in (select f.friendid from friends f, contacts un where f.status='friends' and f.contactid=$user_id and un.id= f.friendid) ORDER BY pt.id DESC LIMIT $offset,$rowsperpage";
        $s = "SELECT *, (select cn.contactname from contacts cn where cn.id = pt.post_by) as posted_by, (select cn.imageurl from contacts cn where cn.id = pt.post_by) as u_image, pt.post_by as user_id, (select cn.contactname from contacts cn where cn.id = pt.post_created_by) as post_created_by_name from trip_post pt where pt.is_del = 0 and pt.is_hide = 0 and pt.post_by = $user_id or pt.post_by in (select fr.contactid from friends fr, contacts unn where fr.status='friends' and fr.friendid=$user_id and unn.id=fr.contactid) or pt.post_by in (select f.friendid from friends f, contacts un where f.status='friends' and f.contactid=$user_id and un.id= f.friendid) ORDER BY pt.id DESC LIMIT $offset,$rowsperpage";
//        echo $s;
        $posts = DB::select($s);
        if (count($posts) > 0) {
            foreach ($posts as $post) {

                $likebyuser = Post_likes::where(['user_id' => $user_id, 'post_id' => $post->id])->first();

                $is_like = isset($likebyuser) ? '1' : '0';

                $media_re = DB::select("select pm.media_url,pm.media_type from post_media pm where pm.post_id=$post->id");

                $comment_re = DB::select("select cm.id, cm.user_id, c.contactname, c.imageurl, cm.description from post_comments cm, contacts c where cm.user_id = c.id and cm.post_id=$post->id");

                $like_re = DB::select("SELECT c.contactname, c.imageurl, pl.user_id FROM post_likes pl, contacts c WHERE pl.user_id = c.id and pl.post_id=$post->id");

                $post_tags = DB::select("SELECT pt.trip_post_id, c.contactname, pt.contactid FROM trip_post_tag pt, contacts c WHERE pt.trip_post_id=$post->id and pt.contactid = c.id");

                $results[] = ['id' => $post->id, 'title' => $post->title, 'visible_till' => $post->visible_till, 'caption' => $post->caption, 'description' => $post->description, 'trip_id' => $post->trip_id, 'user_id' => $post->user_id, 'u_image' => $post->u_image, 'posted_by' => $post->posted_by, 'post_created_by' => $post->post_created_by, 'post_created_by_name' => $post->post_created_by_name, 'created_time' => $post->created_time, 'is_like' => $is_like, 'post_tags' => $post_tags, 'media' => $media_re, 'likes' => count($like_re), 'comments' => count($comment_re)];
            }
            return $this->sendResponse($results, 'Trip Post List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function get_trip_post_by_userid(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user_id = request('user_id');
//        $now_date =
//        $posts = DB::select("select pt.*, c.contactname as posted_by, c.imageurl as u_image, c.id as user_id, pt.created_time from trip_post pt, contacts c where  pt.post_by = c.id and pt.is_del = 0 and pt.is_hide = 0 and pt.post_by =$user_id ORDER BY pt.id DESC");
        $posts = DB::select("SELECT *, (select cn.contactname from contacts cn where cn.id = tp.post_by) as posted_by, (select cn.imageurl from contacts cn where cn.id = tp.post_by) as u_image, tp.post_by as user_id , (select cn.contactname from contacts cn where cn.id = tp.post_created_by) as post_created_by_name FROM trip_post tp WHERE tp.id in (select t.id from trip_post t where t.post_by=$user_id and  tp.is_del = 0 and tp.is_hide = 0 and (t.visible_till is null or  t.visible_till > now())) ORDER BY tp.id DESC");
        $numrows = count($posts);
        $rowsperpage = 10;
        $totalpages = ceil($numrows / $rowsperpage);
        if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
            $currentpage = (int)$_GET['currentpage'];
        } else {
            $currentpage = 1;  // default page number
        }
        if ($currentpage < 1) {
            $currentpage = 1;
        }

        $offset = ($currentpage - 1) * $rowsperpage;
        $s = "SELECT *, (select cn.contactname from contacts cn where cn.id = tp.post_by) as posted_by, (select cn.imageurl from contacts cn where cn.id = tp.post_by) as u_image, tp.post_by as user_id, (select cn.contactname from contacts cn where cn.id = tp.post_created_by) as post_created_by_name FROM trip_post tp WHERE tp.id in (select t.id from trip_post t where t.post_by=$user_id and  tp.is_del = 0 and tp.is_hide = 0 and (t.visible_till is null or  t.visible_till > now())) ORDER BY tp.id DESC LIMIT $offset,$rowsperpage";
//        echo $s;
        $posts = DB::select($s);
        if (count($posts) > 0) {
            foreach ($posts as $post) {

                $likebyuser = Post_likes::where(['user_id' => $user_id, 'post_id' => $post->id])->first();

                $is_like = isset($likebyuser) ? '1' : '0';

                $media_re = DB::select("select pm.media_url,pm.media_type from post_media pm where pm.post_id=$post->id");

                $comment_re = DB::select("select cm.id, cm.user_id, c.contactname, c.imageurl, cm.description from post_comments cm, contacts c where cm.user_id = c.id and cm.post_id=$post->id");

                $like_re = DB::select("SELECT c.contactname, c.imageurl, pl.user_id FROM post_likes pl, contacts c WHERE pl.user_id = c.id and pl.post_id=$post->id");

                $post_tags = DB::select("SELECT pt.trip_post_id, c.contactname, pt.contactid FROM trip_post_tag pt, contacts c WHERE pt.trip_post_id=$post->id and pt.contactid = c.id");

                $results[] = ['id' => $post->id, 'visible_till' => $post->visible_till, 'caption' => $post->caption, 'description' => $post->description, 'trip_id' => $post->trip_id, 'user_id' => $post->user_id, 'u_image' => $post->u_image, 'posted_by' => $post->posted_by, 'post_created_by' => $post->post_created_by, 'post_created_by_name' => $post->post_created_by_name, 'created_time' => $post->created_time, 'is_like' => $is_like, 'post_tags' => $post_tags, 'media' => $media_re, 'likes' => count($like_re), 'comments' => count($comment_re)];
            }
            return $this->sendResponse($results, 'Trip Post List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    /*********Trip Hide**********/
    public
    function get_hidetrip_post_by_userid(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user_id = request('user_id');
        $posts = DB::select("select pt.*, c.contactname as posted_by, c.imageurl as u_image, c.id as user_id, pt.created_time from trip_post pt, contacts c where  pt.post_by = c.id and pt.is_del = 0 and pt.is_hide = 1 and pt.post_by =$user_id ORDER BY pt.id DESC");
        $numrows = count($posts);
        $rowsperpage = 10;
        $totalpages = ceil($numrows / $rowsperpage);
        if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
            $currentpage = (int)$_GET['currentpage'];
        } else {
            $currentpage = 1;  // default page number
        }
        if ($currentpage < 1) {
            $currentpage = 1;
        }

        $offset = ($currentpage - 1) * $rowsperpage;
        $s = "select pt.*, c.contactname as posted_by, c.imageurl as u_image, c.id as user_id, pt.created_time from trip_post pt, contacts c where  pt.post_by = c.id and pt.is_del = 0 and pt.is_hide = 1 and pt.post_by =$user_id ORDER BY pt.id DESC LIMIT $offset,$rowsperpage";
//        echo $s;
        $posts = DB::select($s);
        if (count($posts) > 0) {
            foreach ($posts as $post) {

                $likebyuser = Post_likes::where(['user_id' => $user_id, 'post_id' => $post->id])->first();

                $is_like = isset($likebyuser) ? '1' : '0';

                $media_re = DB::select("select pm.media_url,pm.media_type from post_media pm where pm.post_id=$post->id");

                $comment_re = DB::select("select cm.id, cm.user_id, c.contactname, c.imageurl, cm.description from post_comments cm, contacts c where cm.user_id = c.id and cm.post_id=$post->id");

                $like_re = DB::select("SELECT c.contactname, c.imageurl, pl.user_id FROM post_likes pl, contacts c WHERE pl.user_id = c.id and pl.post_id=$post->id");

                $post_tags = DB::select("SELECT pt.trip_post_id, c.contactname, pt.contactid FROM trip_post_tag pt, contacts c WHERE pt.trip_post_id=$post->id and pt.contactid = c.id");

                $results[] = ['id' => $post->id, 'title' => $post->title, 'description' => $post->description, 'trip_id' => $post->trip_id, 'user_id' => $post->user_id, 'u_image' => $post->u_image, 'posted_by' => $post->posted_by, 'created_time' => $post->created_time, 'is_like' => $is_like, 'post_tags' => $post_tags, 'media' => $media_re, 'likes' => count($like_re), 'comments' => count($comment_re)];
            }
            return $this->sendResponse($results, 'Trip Post List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function hide_unhide_trip_post(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip_post_id = request('trip_post_id');
        $trip_post = TripPost::find($trip_post_id);
        if (isset($trip_post)) {
            if ($trip_post->is_hide == 1) {
                $trip_post->is_hide = 0;
                $trip_post->save();
                return $this->sendResponse([], 'Trip post mark as show');
            } else {
                $trip_post->is_hide = 1;
                $trip_post->save();
                return $this->sendResponse([], 'Trip post mark as hide');
            }
        } else {
            return $this->sendError('No record available', '');
        }
    }

    /*********Trip Hide**********/

    public
    function get_trip_post_by_trip_id(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'trip_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip_id = request('trip_id');
        $contact = DB::select("select * from trip_post where trip_id = $trip_id");
        $s = "select pt.*, c.contactname as posted_by from trip_post pt, contacts c where pt.post_by = c.id and pt.trip_id = $trip_id";
        $posts = DB::select($s);
        if (count($posts) > 0) {
            foreach ($posts as $post) {

                $media_re = DB::select("select pm.media_url,pm.media_type from post_media pm where pm.post_id=$post->id");

                $comment_re = DB::select("select cm.id, cm.user_id, c.contactname, c.imageurl, cm.description from post_comments cm, contacts c where cm.user_id = c.id and cm.post_id=$post->id");

                $like_re = DB::select("SELECT c.contactname, c.imageurl, pl.user_id FROM post_likes pl, contacts c WHERE pl.user_id = c.id and pl.post_id=$post->id");

                $post_tags = DB::select("SELECT * FROM `trip_post_tag` WHERE trip_post_id=$post->id");

                $results[] = ['id' => $post->id, 'title' => $post->title, 'description' => $post->description, 'trip_id' => $post->trip_id, 'post_img' => $post->post_img, 'is_del' => $post->is_del, 'posted_by' => $post->posted_by, 'post_tags' => $post_tags, 'media' => $media_re, 'likes' => count($like_re), 'comments' => count($comment_re)];
            }
            return $this->sendResponse($results, 'Trip Post List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function get_trip_post_by_trip_post_id(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'trip_post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip_post_id = request('trip_post_id');
//        $contact = DB::select("select * from trip_post where trip_id = $trip_id");
//        $s = "select pt.*, c.contactname as posted_by from trip_post pt, contacts c where pt.post_by = c.id and pt.trip_id = $trip_id";
//        $posts = DB::select($s);
        $trip_post = TripPost::find($trip_post_id);
        if (isset($trip_post)) {
//            foreach ($posts as $post) {

            $media_re = DB::select("select pm.media_url,pm.media_type from post_media pm where pm.post_id=$trip_post");

            $comment_re = DB::select("select cm.id, cm.user_id, c.contactname, c.imageurl, cm.description from post_comments cm, contacts c where cm.user_id = c.id and cm.post_id=$trip_post");

            $like_re = DB::select("SELECT c.contactname, c.imageurl, pl.user_id FROM post_likes pl, contacts c WHERE pl.user_id = c.id and pl.post_id=$trip_post");

            $post_tags = DB::select("SELECT * FROM `trip_post_tag` WHERE trip_post_id=$trip_post");

            $results[] = ['id' => $trip_post, 'title' => $trip_post->title, 'description' => $trip_post->description, 'trip_id' => $trip_post->trip_id, 'post_img' => $trip_post->post_img, 'is_del' => $trip_post->is_del, 'posted_by' => $trip_post->posted_by, 'post_tags' => $post_tags, 'media' => $media_re, 'likes' => count($like_re), 'comments' => count($comment_re)];
//            }
            return $this->sendResponse($results, 'Trip Post');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function deletetrip_post(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $trip_post = TripPost::find(request('id'));
        if (isset($trip_post)) {
            $post_media = Post_media::where(['post_id' => $trip_post->id])->get();
            if (count($post_media) > 0) {
                foreach ($post_media as $media) {
                    $image_path = $media->media_url;
                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                Post_media::where(['post_id' => $trip_post->id])->delete();
            }
            Post_likes::where(['post_id' => $trip_post->id])->delete();
            Comments::where(['post_id' => $trip_post->id])->delete();
            $trip_post->delete();
            return $this->sendResponse($trip_post, 'Trip post has been deleted');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function create_trip_post_tag(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_post_id' => 'required',
            'tagid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $tids = explode(",", request('tagid'));
        $trip_post_id = request('trip_post_id');
        foreach ($tids as $tagid) {
            $trip_pt = new TripPostTags();
            $trip_pt->trip_post_id = $trip_post_id;
            $trip_pt->contactid = $tagid;
            $trip_pt->save();
        }
        return $this->sendResponse([], 'Tagged has been saved');
    }

    public
    function deletetrip_posttag(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_post_id' => 'required',
            'tagid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $tids = explode(",", request('tagid'));
        $trip_post_id = request('trip_post_id');
        foreach ($tids as $tagid) {
            $tag = TripPostTags::where(['trip_post_id' => $trip_post_id, 'contactid' => $tagid])->delete();
//            if (isset($tag) > 0) {
//                $tag->delete();
//                return $this->sendResponse([], 'Tagged has been removed');
//            } else {
//                return $this->sendError('No record available', '');
//            }
        }
        return $this->sendResponse([], 'Tagged has been removed');
    }


    /*****post like****/
    public
    function post_like(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'post_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $post_id = request('post_id');
        $user_id = request('user_id');
        $postunlike = Post_likes::where(['post_id' => $post_id, 'user_id' => $user_id])->first();
        if (!isset($postunlike)) {
            $pl = new Post_likes();
            $pl->post_id = $post_id;
            $pl->user_id = $user_id;
            $pl->save();
            return $this->sendResponse([], 'You like a post');
        } else {
            $postunlike->delete();
            return $this->sendResponse([], 'You unlike a post');
        }
    }

    public
    function post_comment(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'post_id' => 'required',
            'description' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $post_id = request('post_id');
        $description = request('description');
        $user_id = request('user_id');
        $pl = new Comments();
        $pl->post_id = $post_id;
        $pl->description = $description;
        $pl->user_id = $user_id;
        $pl->save();
        return $this->sendResponse($pl, 'Comment has been saved');
    }

    /*****post like****/
    /**************Trip Post Master**********************/


    /**************Friend List**********************/
    public
    function friend_list(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user_id = request('user_id');
        $friendlist = DB::select("select u.id as fid, u.contactname, u.imageurl as profile_pic from contacts u where u.id in (select f.friendid from friends f where f.contactid=$user_id and f.status='friends') or u.id in (select f.contactid from friends f where f.friendid=$user_id and f.status='friends')");
        if (count($friendlist) > 0) {
            return $this->sendResponse($friendlist, 'Friend List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function like_list(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $post_id = request('post_id');
        $likelist = DB::select("select u.id, u.contactname, u.imageurl as profile_pic from contacts u, post_likes pl where  pl.user_id = u.id and pl.post_id=$post_id");
        if (count($likelist) > 0) {
            return $this->sendResponse($likelist, 'Likes List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function comment_list(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $post_id = request('post_id');
        $likelist = DB::select("select u.id, u.contactname, u.imageurl as profile_pic, pc.description from contacts u, post_comments pc where pc.user_id = u.id and pc.post_id=$post_id");
        if (count($likelist) > 0) {
            return $this->sendResponse($likelist, 'Comments List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    /**************Friend List**********************/


    /**************Request Accept/Reject/Cancel/Unfriend**********************/


    /**************Friends Master**********************/
    public
    function checkrequest(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'contactid' => 'required',
            'friendid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user_id = request('contactid');
        $friend_id = request('friendid');
        $friend = DB::select("select f.status as status from friends f where (f.contactid = $user_id and f.friendid = $friend_id or f.contactid = $friend_id and f.friendid = $user_id)");
        if ($friend != null) {
            if ($friend[0]->status == 'pending') {
                $queryResult = DB::select("call GetFriendType($user_id,$friend_id)");
                $result = collect($queryResult);
                $contact = Contacts::find($friend_id);
                $this->getfriend($friend_id);
                $res = ['status' => $result[0], 'user_info' => $contact];
                return $this->sendResponse($result[0], 'Request status');
            } else {
                $contact = Contacts::find($friend_id);
                $res = ['status' => $friend[0]->status, 'user_info' => $contact];
                return $this->sendResponse($res, 'Request status');
            }
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function create_friend(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
            'friendid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $friends = new Friends();
        $friends->contactid = request('contactid');
        $friends->friendid = request('friendid');
        $friends->status = 'Pending';
        $friends->save();
        return $this->sendResponse($friends, 'Friend Request has been sent');
    }

    public
    function cancelrequest(Request $request) ////by user send/cancel button
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
            'friendid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $friend = Friends::where(['contactid' => request('contactid'), 'friendid' => request('friendid'), 'status' => 'pending'])->first();
        if (isset($friend)) {
            $friend->delete();
            return $this->sendResponse([], 'Request Cancelled');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function acceptrequest(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
            'friendid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $friend = Friends::where(['contactid' => request('friendid'), 'friendid' => request('contactid')])->first();
        if (isset($friend)) {
            $friend->status = 'friends';
            $friend->save();
            return $this->sendResponse($friend, 'Request has been accepted');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function getfriends(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contactid = request('contactid');
        $friends = DB::select("select c.id,c.contactname,c.mobile,c.contactstatus,c.imageurl from contacts c where c.isactive=1 and c.id in (select f.friendid from friends f where f.status='friends' and f.contactid=$contactid) or 
id in (select f.contactid from friends f where f.status='friends' and f.friendid=$contactid)");
        if (isset($friends) > 0) {
            return $this->sendResponse($friends, 'Friends List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function block(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'friendid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $friends = Friends::where(['friendid' => request('friendid')])->first();
        if (isset($friends) > 0) {
            $friends->status = 'block';
            $friends->save();
            return $this->sendResponse($friends, 'Friend has been blocked');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function getblockedfriends(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contactid = request('contactid');
        $friends = DB::select("select c.id,c.contactname,c.mobile,c.contactstatus,c.imageurl from contacts c where c.isactive=1 and c.id in (select f.friendid from friends f where f.status='block' and f.contactid=$contactid) or id in (select f.contactid from friends f where f.status='block' and f.friendid=$contactid)");
        if (isset($friends) > 0) {
            return $this->sendResponse($friends, 'Block Friends List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function deletefriend(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
            'friendid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $friend = Friends::where(['contactid' => request('contactid'), 'friendid' => request('friendid')])->first();
        if (isset($friend)) {
            $friend->delete();
            return $this->sendResponse([], 'Unfriend successful');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public
    function requestlist(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contactid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user_id = request('contactid');
        $requestlist = DB::select("select c.id,c.contactname,c.mobile,c.contactstatus,c.imageurl from contacts c where c.isactive=1 and c.id in (select f.contactid from friends f where f.status='pending' and f.friendid=$user_id)");
        if (count($requestlist) > 0) {
            return $this->sendResponse($requestlist, 'Request List');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function make_as_read_noti()
    {
        $noti = UserNotifications::find(request('notification_id'));
        if (isset($noti)) {
            $noti->seen = 1;
            $noti->save();
            return $this->sendResponse($noti, 'Notification marked as read');
        } else {
            return $this->sendError('No record available', '');
        }
    }


    public function remove_noti()
    {
        $noti = UserNotifications::find(request('notification_id'));
        if (isset($noti)) {
            $noti->delete();
            return $this->sendResponse([], 'Notification has been removed');
        } else {
            return $this->sendError('No record available', '');
        }
    }

    public function getusernotification()
    {
        $user_id = request('user_id');
//        $user_notifications = DB::select("select t.name, (select c.state from cities c where u.state_id = c.CID) as state, (select c.state from cities c where u.city_id = c.CID) as city from users u, timeline t, notifications n where  u.timeline_id = t.id and n.notified_by = u.id and n.user_id = '$user_id'");
        $user_notifications = UserNotifications::where(['user_id' => $user_id])->orderBy('id', 'desc')->get();
        $results = [];
        if (count($user_notifications) > 0) {
            foreach ($user_notifications as $user_notification) {
                $user = Contacts::find($user_notification->notified_by);
                $results[] = ['id' => $user_notification->id, 'post_id' => $user_notification->post_id, 'description' => $user_notification->description, 'user_id' => $user_notification->user_id, 'notified_by' => $user_notification->notified_by, 'seen' => $user_notification->seen, 'notified_by_name' => ucwords($user->contactname), 'notify_by_pic' => $user->imageurl, 'created_at' => $user_notification->created_at];
            }
            return $this->sendResponse($results, 'Notification List');
        } else {
            return $this->sendError('No record available', '');
        }
    }



    /**************Friends Master**********************/
    /**************Request Accept/Reject/Cancel/Unfriend**********************/

}
