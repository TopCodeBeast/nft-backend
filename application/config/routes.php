<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'admin';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['api/v1/register']['post']              = 'api/v1/User/register';
$route['api/v1/login']['post']                 = 'api/v1/User/login';
$route['api/v1/forgotpassword']['post']              = 'api/v1/User/forgotpassword';
$route['api/v1/Listusers']['post']                 = 'api/v1/User/Listusers';
$route['api/v1/profile']['post']              = 'api/v1/User/profile';
$route['api/v1/profile_pic']['post']              = 'api/v1/User/profile_pic';
$route['api/v1/get_profile_pic/(:any)']['get']  = 'api/v1/User/get_profile_pic/$1';
$route['api/v1/UploadAvatar']['post']                 = 'api/v1/User/UploadAvatar';

$route['api/v1/get_notification']['post']  = 'api/v1/User/get_notification';
$route['api/v1/accept_or_reject']['post']              = 'api/v1/User/accept_or_reject';

$route['api/v1/statusbar']['post']                 = 'api/v1/User/statusbar';
$route['api/v1/GetStatus']['post']                 = 'api/v1/User/GetStatus';
$route['api/v1/MatchInvitation']['post']                 = 'api/v1/User/MatchInvitation';
$route['api/v1/Match_reply']['post']                 = 'api/v1/User/Match_reply';
$route['api/v1/AllInvitation']['post']                 = 'api/v1/User/AllInvitation';
$route['api/v1/OngoingMatch']['post']                 = 'api/v1/User/OngoingMatch';
$route['api/v1/Like']['post']                 = 'api/v1/User/Like';
$route['api/v1/PersonalMatch']['post']                 = 'api/v1/User/PersonalMatch';
$route['api/v1/Comment']['post']                 = 'api/v1/User/Comment';
$route['api/v1/GetComment']['post']                 = 'api/v1/User/GetComment';
$route['api/v1/OpenMatch']['post']                 = 'api/v1/User/OpenMatch';
$route['api/v1/add_fan']['post']              = 'api/v1/User/add_fan';
$route['api/v1/RemoveFans']['post']              = 'api/v1/User/RemoveFans';
$route['api/v1/Headline']['post']              = 'api/v1/User/Headline';
$route['api/v1/ActivityPage']['post']              = 'api/v1/User/ActivityPage';

$route['api/v1/GetAdminMessage']['post']              = 'api/v1/User/GetAdminMessage';
$route['api/v1/AnswerMessage']['post']              = 'api/v1/User/AnswerMessage';
$route['api/v1/CommentLike']['post']              = 'api/v1/User/CommentLike';
$route['api/v1/MyiQuery']['post']              = 'api/v1/User/MyiQuery';
$route['api/v1/GetMyiQuery']['post']                 = 'api/v1/User/GetMyiQuery';
$route['api/v1/CommentAnswer']['post']                 = 'api/v1/User/CommentAnswer';
$route['api/v1/RemoveiQuery']['post']              = 'api/v1/User/RemoveiQuery';
$route['api/v1/Myiquerylike']['post']              = 'api/v1/User/Myiquerylike';
$route['api/v1/Iquerycomment']['post']              = 'api/v1/User/Iquerycomment';
$route['api/v1/GetMyiQueryComment']['post']                 = 'api/v1/User/GetMyiQueryComment';
$route['api/v1/Top100']['post']                 = 'api/v1/User/Top100';
$route['api/v1/BannerSearch']['post']                 = 'api/v1/User/BannerSearch';
$route['api/v1/winners_of_the_moment']['post']                 = 'api/v1/User/winners_of_the_moment';

$route['api/v1/Recommended_for_you']['post']                 = 'api/v1/User/Recommended_for_you';

$route['api/v1/Upload_File']['post']                 = 'api/v1/User/Upload_File';
$route['api/v1/Chat']['post']                 = 'api/v1/User/Chat';
$route['api/v1/GetChat']['post']                 = 'api/v1/User/GetChat';
$route['api/v1/GetDetailChat']['post']                 = 'api/v1/User/GetDetailChat';
$route['api/v1/Online_Offline_status']['post']                 = 'api/v1/User/Online_Offline_status';
$route['api/v1/BestMoment']['post']                 = 'api/v1/User/BestMoment';
$route['api/v1/UploadBestieMomentFile']['post']                 = 'api/v1/User/UploadBestieMomentFile';
$route['api/v1/updateUser']['post']                 = 'api/v1/User/save';

$route['api/v1/GetBestMoment']['post']                 = 'api/v1/User/GetBestMoment';
$route['api/v1/MatchFileUpload']['post']                 = 'api/v1/User/MatchFileUpload';
$route['api/v1/UploadChatFile']['post']                 = 'api/v1/User/UploadChatFile';
$route['api/v1/MomentLike']['post']                 = 'api/v1/User/MomentLike';
$route['api/v1/MomentComment']['post']                 = 'api/v1/User/MomentComment';
$route['api/v1/GetMomentComment']['post']                 = 'api/v1/User/GetMomentComment';
$route['api/v1/acceptInvitation']['post']                 = 'api/v1/User/acceptInvitation';
$route['api/v1/GetUserdetails']['post']                 = 'api/v1/User/GetUserdetails';
// $route['api/v1/Sample']['get']              = 'api/v1/User/Sample';
// $route['api/v1/GetMap']['post']              = 'api/v1/User/GetMap';
$route['api/v1/ReplyComment']['post']                 = 'api/v1/User/ReplyComment';
$route['api/v1/MatchCommentLike']['post']              = 'api/v1/User/MatchCommentLike';
$route['api/v1/CreateOpenMatch']['post']                 = 'api/v1/User/CreateOpenMatch';
$route['api/v1/create_personalmatch']['post']                 = 'api/v1/User/create_personalmatch';
$route['api/v1/create_closedmatch']['post']                 = 'api/v1/User/create_closedmatch';
$route['api/v1/ClosedMatch']['post']                 = 'api/v1/User/ClosedMatch';
$route['api/v1/personallike']['post']                 = 'api/v1/Like/personallike';
$route['api/v1/RemoveBesties']['post']              = 'api/v1/User/RemoveBesties';

$route['api/v1/DeleteBestieComment']['post']              = 'api/v1/User/DeleteBestieComment';
$route['api/v1/BestieCommentLike']['post']              = 'api/v1/User/BestieCommentLike';
$route['api/v1/UpdateBestieComment']['post']              = 'api/v1/User/UpdateBestieComment';
$route['api/v1/disable_comment']['post']              = 'api/v1/User/disable_comment';
$route['api/v1/get_pending_match/(:any)']['get']  = 'api/v1/User/get_pending_match/$1';

$route['api/v1/startPendingMatch']['post']              = 'api/v1/User/startPendingMatch';
$route['api/v1/get_ongoing_match/(:any)']['get']              = 'api/v1/Match/getOngoingMatch/$1';

// Notification API
$route['api/v1/getNotification/(:any)']['get']              = 'api/v1/Notification/getNotification/$1';
$route['api/v1/setNotificationRead']['post']              = 'api/v1/Notification/setNotificationRead';
$route['api/v1/getNotifications']['post']              = 'api/v1/Notification/all';


// Bestie API
$route['api/v1/getBestieRequest']['post']              = 'api/v1/Bestie/getBestieRequest';
$route['api/v1/add_bestie']['post']              = 'api/v1/Bestie/add_bestie';
$route['api/v1/changeBestieStatus']['post']              = 'api/v1/Bestie/changeBestieStatus';
$route['api/v1/getSquadList']['post']              = 'api/v1/Bestie/getSquadList';
$route['api/v1/getBestieList']['post']                 = 'api/v1/Bestie/getBestieList';
$route['api/v1/BestieAction']['post']              = 'api/v1/Bestie/getActionLog';

// Bestie Comment
$route['api/v1/BestieComment']['post']              = 'api/v1/BestieComment/add';
$route['api/v1/GetBestieComment']['post']              = 'api/v1/BestieComment/all';


// Bestie Like
$route['api/v1/bestielike']['post']                 = 'api/v1/Bestielike/add';


