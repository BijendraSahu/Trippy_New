<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::GET('/', function () {
    return view('welcome');
});

/*************API******************/
//Location Request
Route::get('create_location', 'APIController@create_location');
Route::get('updatelocation', 'APIController@updatelocation');
Route::get('deletelocation', 'APIController@deletelocation');
Route::get('getlocation_byid', 'APIController@getlocation_byid');
Route::get('getlocation_bycontactid', 'APIController@getlocation_bycontactid');
//Location Request





//Trips Request
Route::get('create_trips', 'APIController@create_trips');
Route::get('updatetrips', 'APIController@updatetrips');
Route::get('getalltrips', 'APIController@getalltrips');
Route::get('get_my_trip', 'APIController@get_my_trip');
Route::get('deletetrips', 'APIController@deletetrips');
Route::get('gettrips_byid', 'APIController@gettrips_byid');
//Trips Request


//TripMembers Request
Route::get('create_tripmembers', 'APIController@create_tripmembers');
Route::get('get_tripfriends', 'APIController@get_tripfriends');
Route::get('deletetripmembers', 'APIController@deletetripmembers');
//TripMembers Request


//Contacts Request
Route::post('create_contact', 'APIController@create_contact');
Route::post('updatecontact', 'APIController@updatecontact');
Route::get('get_all_contacts', 'APIController@get_all_contacts');
Route::get('deletecontact', 'APIController@deletecontact');
Route::get('get_contact_by_mobile', 'APIController@get_contact_by_mobile');
Route::get('resendotp', 'APIController@resendotp');
Route::get('searchuser', 'APIController@searchuser');
Route::get('setprivacy', 'APIController@setprivacy');
//Contacts Request


//Trip Post Request
Route::get('share_trip_post', 'APIController@share_trip_post');
Route::post('create_trip_post', 'APIController@create_trip_post');
Route::get('edit_trip_post', 'APIController@edit_trip_post');
Route::post('updatetrip_post', 'APIController@updatetrip_post');
Route::get('get_Alltrip_posts', 'APIController@get_Alltrip_posts');
Route::get('get_trip_post_by_trip_id', 'APIController@get_trip_post_by_trip_id');
Route::get('get_trip_post_by_trip_post_id', 'APIController@get_trip_post_by_trip_post_id');
Route::get('get_trip_post_by_userid', 'APIController@get_trip_post_by_userid');
Route::get('get_hidetrip_post_by_userid', 'APIController@get_hidetrip_post_by_userid');
Route::get('hide_unhide_trip_post', 'APIController@hide_unhide_trip_post');
Route::get('deletetrip_post', 'APIController@deletetrip_post');

Route::get('create_trip_post_tag', 'APIController@create_trip_post_tag');
Route::get('deletetrip_posttag', 'APIController@deletetrip_posttag');

Route::get('setpostprivacy', 'APIController@setpostprivacy');


//post Like/Comment
Route::get('post_like', 'APIController@post_like');
Route::get('post_comment', 'APIController@post_comment');

//post Like/Comment


//Trip Post Request

//Friends
Route::get('friend_list', 'APIController@friend_list');
Route::get('like_list', 'APIController@like_list');
Route::get('comment_list', 'APIController@comment_list');
//Friends


//Request Accept/Reject/Cancel/Unfriend
Route::get('sendrequest', 'APIController@sendrequest'); //send_request
Route::get('acceptrequest', 'APIController@acceptrequest'); //acceptrequest
Route::get('requestlist', 'APIController@requestlist'); //requestlist


//Friends Request
Route::get('checkrequest', 'APIController@checkrequest'); //checkrequest
Route::get('sendrequest', 'APIController@create_friend');
Route::get('acceptrequest', 'APIController@acceptrequest');
Route::get('cancelrequest', 'APIController@cancelrequest');
Route::get('getfriends', 'APIController@getfriends');
Route::get('block', 'APIController@block');
Route::get('getblockedfriends', 'APIController@getblockedfriends');
Route::get('requestlist', 'APIController@requestlist');
Route::get('unfriend', 'APIController@deletefriend'); //unfrind
//Friends Request
//Request Accept/Reject/Cancel/Unfriend


//Notification
Route::post('google_fb_login', 'APIController@google_fb_login'); //user notification read
Route::get('make_as_read_noti', 'APIController@make_as_read_noti'); //user notification read
Route::get('remove_noti', 'APIController@remove_noti'); //user notification remove
Route::get('getusernotification', 'APIController@getusernotification'); //user notification
//Notification

/*************API******************/


