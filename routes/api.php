<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\Blog\BlogApiController;
use App\Http\Controllers\Api\PackageApiController;
use App\Http\Controllers\Api\ServicePlanApiController;
use App\Http\Controllers\Api\HeaderMenuApiController;
use App\Http\Controllers\Api\FooterApiController;
use App\Http\Controllers\Api\FaqApiController;
use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Api\HeroBannerApiController;
use App\Http\Controllers\Api\ReviewApiController;
use App\Http\Controllers\Api\About\CoreValueApiController;
use App\Http\Controllers\Api\About\MissionValueApiController;
use App\Http\Controllers\Api\About\WhyPlatformApiController;
use App\Http\Controllers\Api\InquiryApiController;
use App\Http\Controllers\Api\PageSectionApiController;
use App\Http\Controllers\Api\ContactDetailApiController;
use App\Http\Controllers\AngelData\AngelController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Customer\CustomerDetailsController;
use App\Http\Controllers\Api\Chat\UserChatApiController;
use App\Http\Controllers\Api\Ticket\TicketApiController;
use App\Http\Controllers\Api\Notification\NotificationApiController;
use App\Http\Controllers\Api\Customer\ProfileApiController;
use App\Http\Controllers\Api\Popups\CampaignApiController;
use App\Http\Controllers\Api\Tips\CategoryController;

use App\Http\Controllers\Api\Banners\MarqueeApiController;
use App\Http\Controllers\Api\News\NewsApiController;
use App\Http\Controllers\Api\News\NewsCategoryApiController;
use App\Http\Controllers\Api\Banners\OfferBannerApiController;
use App\Http\Controllers\Api\Kyc\MobileKycController;
use App\Http\Controllers\Api\Subscription\MobileSubscriptionController;
use App\Http\Controllers\Api\Customer\DeleteController;
use App\Http\Controllers\Api\Watchlist\WatchlistController;
use App\Http\Controllers\Api\Notification\AllNotificationApi;
use App\Http\Controllers\Api\Popups\PopupController;
use App\Http\Controllers\Api\Announcement\AnnouncementController;
use App\Http\Controllers\Api\PolicyController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PolicyAcceptance\PolicyAcceptanceController;

use App\Http\Controllers\Api\Auth\ForgotPasswordController;

Route::prefix('auth')->group(function () {
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
});

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('acceptance', [PolicyAcceptanceController::class, 'show']);
            Route::post('policy/accept', [PolicyAcceptanceController::class, 'accept']);

        
        });


                // Public
                Route::get('/reviews', [ReviewController::class, 'index']);
                
                // Auth Required
                Route::middleware('auth:sanctum')->group(function () {
                    Route::post('/reviews', [ReviewController::class, 'store']);
                    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
                    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
                });
                    
        
                Route::get('/policies', [PolicyController::class, 'index']);
                Route::get('/policies/{slug}', [PolicyController::class, 'show']);

            
                //-----------------------------------------
                    // Authentication Api's 
                //-----------------------------------------
                Route::prefix('auth')->group(function () {
                    Route::post('login', [LoginController::class, 'login']);
                    Route::middleware('auth:sanctum')->post('logout', [LogoutController::class, 'logout']);
                });
                Route::get('/userTokenTest', [UserApiController::class, 'userTokenTest']);
                Route::prefix('auth/register')->group(function () {
                    Route::post('details', [RegisterController::class, 'storeDetails']);
                    Route::post('phone', [RegisterController::class, 'sendOtp']);
                    Route::post('verify-otp', [RegisterController::class, 'verifyOtp']);
                });

                Route::prefix('angel')->group(function () {

                    // Auth
                    Route::get('login', [AngelController::class, 'login']);
                    Route::get('ws-token', [AngelController::class, 'wsToken']);

                    // Market Data
                    Route::get('history', [AngelController::class, 'history']);
                    Route::get('gainers-losers', [AngelController::class, 'gainersLosers']);
                    Route::get('indices', [AngelController::class, 'getIndices']);
                    Route::get('nifty50-marquee', [AngelController::class, 'nifty50Marquee']);
                    Route::get('52-week-data', [AngelController::class, 'fetch52WeekHighLowData']);

                    // Cascading dropdowns
                    Route::get('find-token', [AngelController::class, 'findToken']);
                    Route::get('search-names', [AngelController::class, 'searchNames']);
                    Route::get('expiries', [AngelController::class, 'getExpiries']);
                    Route::get('get-strikes', [AngelController::class, 'getStrikes']);
                    Route::get('quote', [AngelController::class, 'quote']);
                    Route::get('search-equity-names', [AngelController::class, 'searchEquityNames']);
                    Route::get('find-equity-token', [AngelController::class, 'findEquityToken']);
                });

                // Public
                Route::post('send-otp', [AuthApiController::class, 'sendOtp']);
                Route::post('verify-otp', [AuthApiController::class, 'verifyOtp']);

                Route::middleware('auth:sanctum')->group(function () {
                    Route::post('logout', [AuthApiController::class, 'logout']);
                    Route::get('user', [UserApiController::class, 'user']);
                    Route::put('user/update', [UserApiController::class, 'update']); // Update current user
                    Route::put('user/{id}', [UserApiController::class, 'update']);

                    // ⭐ TEST API
                    Route::get('test-api', function () {
                        return response()->json([
                            'success' => true,
                            'message' => 'Sanctum token verified successfully!',
                            'user' => Auth::user(),
                        ]);
                    });
                });


                // blogs api
                Route::middleware('auth:sanctum')->group(function () {
                    Route::get('blogs', [BlogApiController::class, 'index']);
                    Route::get('blogs/{id}', [BlogApiController::class, 'show']);
                    Route::post('blogs', [BlogApiController::class, 'store']);
                    Route::put('blogs/{id}', [BlogApiController::class, 'update']);
                    Route::delete('blogs/{id}', [BlogApiController::class, 'destroy']);
                });


                // packages api
                Route::middleware('auth:sanctum')->prefix('packages')->group(function () {
                    Route::get('/',            [PackageApiController::class, 'index']);
                    Route::get('/{id}',        [PackageApiController::class, 'show']);
                    Route::post('/',           [PackageApiController::class, 'store']);
                    Route::put('/{id}',       [PackageApiController::class, 'update']);
                    Route::delete('/{id}',     [PackageApiController::class, 'destroy']);
                });


                // service plans api
                Route::middleware('auth:sanctum')->prefix('service-plans')->group(function () {

                    Route::get('/', [ServicePlanApiController::class, 'index']);
                    Route::get('/{id}', [ServicePlanApiController::class, 'show']);
                    Route::post('/', [ServicePlanApiController::class, 'store']);
                    Route::put('/{id}', [ServicePlanApiController::class, 'update']);
                    Route::delete('/{id}', [ServicePlanApiController::class, 'destroy']);

                    // MULTIPLE DELETE
                    Route::post('/multi-delete', [ServicePlanApiController::class, 'multiDelete']);
                });


                // header menus api
                Route::middleware('auth:sanctum')->prefix('header-menus')->group(function () {

                    Route::get('/', [HeaderMenuApiController::class, 'index']);
                    Route::post('/', [HeaderMenuApiController::class, 'store']);
                    Route::get('/settings', [HeaderMenuApiController::class, 'settings']);

                    Route::patch('/{menu}/toggle', [HeaderMenuApiController::class, 'toggleStatus']);
                    Route::patch('/{menu}/quick-update', [HeaderMenuApiController::class, 'quickUpdate']);
                    Route::delete('/{menu}', [HeaderMenuApiController::class, 'destroy']);
                });


                // footer api
                Route::middleware('auth:sanctum')->prefix('footer')->group(function () {

                    Route::get('/', [FooterApiController::class, 'index']);

                    // settings
                    Route::post('/settings/update', [FooterApiController::class, 'updateSettings']);

                    // columns
                    Route::post('/column/store', [FooterApiController::class, 'storeColumn']);
                    Route::post('/column/update/{id}', [FooterApiController::class, 'updateColumn']);
                    Route::delete('/column/delete/{id}', [FooterApiController::class, 'deleteColumn']);
                    Route::post('/column/reorder', [FooterApiController::class, 'reorderColumns']);

                    // links
                    Route::post('/link/store', [FooterApiController::class, 'storeLink']);
                    Route::post('/link/update/{id}', [FooterApiController::class, 'updateLink']);
                    Route::delete('/link/delete/{id}', [FooterApiController::class, 'deleteLink']);
                    Route::post('/link/reorder', [FooterApiController::class, 'reorderLinks']);

                    // socials
                    Route::post('/social/store', [FooterApiController::class, 'storeSocial']);
                    Route::post('/social/update/{id}', [FooterApiController::class, 'updateSocial']);
                    Route::delete('/social/delete/{id}', [FooterApiController::class, 'deleteSocial']);
                    Route::post('/social/reorder', [FooterApiController::class, 'reorderSocial']);
                });

                // About Api Routes for these sections 
                Route::middleware('auth:sanctum')->prefix('about')->group(function () {

                    // ===== CORE VALUES =====
                    Route::get('core-values', [CoreValueApiController::class, 'index']);
                    Route::post('core-values/section', [CoreValueApiController::class, 'storeSection']);
                    Route::post('core-values/value', [CoreValueApiController::class, 'storeValue']);
                    Route::put('core-values/value/{id}', [CoreValueApiController::class, 'updateValue']);
                    Route::delete('core-values/value/{id}', [CoreValueApiController::class, 'deleteValue']);

                    // ===== MISSION =====
                    Route::get('mission', [MissionValueApiController::class, 'show']);
                    Route::post('mission', [MissionValueApiController::class, 'storeOrUpdate']);
                    Route::put('mission/{id}', [MissionValueApiController::class, 'delete']);


                // ===== WHY PLATFORM =====
                    Route::get('why-platform', [WhyPlatformApiController::class, 'index']);  
                    Route::post('why-platform', [WhyPlatformApiController::class, 'store']); 
                    Route::put('why-platform/{id}', [WhyPlatformApiController::class, 'update']);
                    Route::delete('why-platform/{id}', [WhyPlatformApiController::class, 'deleteSection']); 
                });

                // FAQ API Routes 
                Route::middleware('auth:sanctum')->prefix('faq')->group(function () {

                    Route::get('/', [FaqApiController::class, 'index']);          
                    Route::post('/', [FaqApiController::class, 'store']);       
                    Route::put('/{id}', [FaqApiController::class, 'update']);    
                    Route::delete('/{id}', [FaqApiController::class, 'destroy']); 
                });

                // Hero Banner API Routes 
                Route::middleware('auth:sanctum')->prefix('hero-banners')->group(function () {

                    Route::get('/', [HeroBannerApiController::class, 'index']);                
                    Route::get('/page/{page_key}', [HeroBannerApiController::class, 'byPage']); 
                    Route::post('/', [HeroBannerApiController::class, 'store']);              
                    Route::put('/{id}', [HeroBannerApiController::class, 'update']);           
                    Route::delete('/{id}', [HeroBannerApiController::class, 'destroy']);       
                    Route::post('/reorder', [HeroBannerApiController::class, 'reorder']);      
                    // Media APIs (Spatie)
                    Route::get('/media', [HeroBannerApiController::class, 'mediaApi']);
                    Route::post('/media/upload', [HeroBannerApiController::class, 'mediaUpload']);
                });



                // Home Page All Sections API Routes
                Route::middleware('auth:sanctum')->prefix('home')->group(function () {

                    // Counters
                    Route::get('counters', [HomeApiController::class, 'counters']);
                    Route::post('counters', [HomeApiController::class, 'counterStore']);
                    Route::put('counters/{id}', [HomeApiController::class, 'counterUpdate']);
                    Route::delete('counters/{id}', [HomeApiController::class, 'counterDelete']);
                    Route::post('counters/{id}/toggle', [HomeApiController::class, 'counterToggle']);
                    Route::post('counters/reorder', [HomeApiController::class, 'counterReorder']);

                    // Why Choose
                    Route::get('why-choose', [HomeApiController::class, 'whyChoose']);
                    Route::post('why-choose', [HomeApiController::class, 'whyChooseSave']);
                    Route::delete('why-choose/{id}', [HomeApiController::class, 'whyChooseDelete']);
                    Route::post('why-choose/{id}/toggle', [HomeApiController::class, 'whyChooseToggle']);
                    Route::post('why-choose/reorder', [HomeApiController::class, 'whyChooseReorder']);

                    // How It Works
                    Route::get('how-it-works', [HomeApiController::class, 'howItWorks']);
                    Route::post('how-it-works', [HomeApiController::class, 'howItWorksSave']);

                    // Key Features
                    Route::get('key-features', [HomeApiController::class, 'keyFeatures']);
                    Route::post('key-features', [HomeApiController::class, 'keyFeatureUpdate']);
                    Route::post('key-features/item', [HomeApiController::class, 'keyFeatureItemStore']);
                    Route::delete('key-features/item/{id}', [HomeApiController::class, 'keyFeatureItemDelete']);
                    Route::post('key-features/reorder', [HomeApiController::class, 'keyFeatureReorder']);

                    // Download App
                    Route::get('download-app/{page_key}', [HomeApiController::class, 'downloadApp']);
                    Route::post('download-app', [HomeApiController::class, 'downloadAppStore']);
                });


                // Reviews API Routes
                Route::middleware('auth:sanctum')->prefix('reviews')->group(function () {

                    Route::get('/', [ReviewApiController::class, 'index']);          
                    Route::post('/', [ReviewApiController::class, 'store']);  
                    Route::put('/{id}', [ReviewApiController::class, 'update']);    
                    Route::delete('/{id}', [ReviewApiController::class, 'destroy']); 
                });

                // Inquiry API Routes
                Route::post('inquiries', [InquiryApiController::class, 'store'])->middleware('auth:sanctum');



                // Page Sections API Routes
                Route::middleware('auth:sanctum')->prefix('page-sections')->group(function () {
                    Route::get('{page_key}', [PageSectionApiController::class, 'index']);
                    Route::post('/', [PageSectionApiController::class, 'store']);
                    Route::delete('{id}', [PageSectionApiController::class, 'destroy']);
                });


                // Contact Details API Routes
                Route::middleware('auth:sanctum')->prefix('contact-details')->group(function () {
                    Route::get('/', [ContactDetailApiController::class, 'show']);   // Fetch
                    Route::post('/', [ContactDetailApiController::class, 'store']); // Create / Update
                });



                Route::middleware('auth:sanctum')->get('/customer/profile',[CustomerDetailsController::class, 'index'])->name('customer.profile');



                // User Chat Api 
                Route::middleware('auth:sanctum')->prefix('chat')->group(function () {
                    Route::get('/history', [UserChatApiController::class, 'history']);
                    Route::post('/send', [UserChatApiController::class, 'sendMessage']);
                    Route::post('/notifications/read/{id}', [UserChatApiController::class, 'markNotificationRead']);
                    Route::post('/notifications/read-all', [UserChatApiController::class, 'markAllNotificationsRead']);
                });


                // Ticket Raise Api

                Route::middleware('auth:sanctum')->prefix('ticket')->group(function () {
                    Route::get('/list', [TicketApiController::class, 'index']);
                    Route::post('/store', [TicketApiController::class, 'store']);
                    Route::get('/{id}', [TicketApiController::class, 'show']);

                });


                // All Notifications Unread for header 

                Route::middleware('auth:sanctum')->group(function () {
                    
                    // Notification Bell Icon Routes
                    Route::get('/notifications/unseen', [NotificationApiController::class, 'fetchUnseen']);
                    Route::post('/notifications/mark-read/{id}', [NotificationApiController::class, 'markAsRead']);

                });

		
                // AllNotificationsApi
                Route::middleware('auth:sanctum')->prefix('user/notifications')->group(function () {
                Route::get('/', [AllNotificationApi::class, 'index']);
                Route::post('read', [AllNotificationApi::class, 'markRead']);
                Route::post('read-all', [AllNotificationApi::class, 'markAllRead']);
                Route::post('delete', [AllNotificationApi::class, 'delete']);
                Route::get('unread-count', [AllNotificationApi::class, 'unreadCount']);
            });
                // Update the mobile and email on customer profile 

                Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
                    Route::get('/', [ProfileApiController::class, 'getProfile']);
                    Route::post('/update', [ProfileApiController::class, 'updateGeneralProfile']);
                    Route::post('/otp/send', [ProfileApiController::class, 'sendUpdateOtp']);
                    Route::post('/otp/verify', [ProfileApiController::class, 'verifyAndUpdate']);

                    // 1. Password Reset OTP bhejne ke liye
                    Route::post('/password/send-otp', [ProfileApiController::class, 'sendPasswordOtp']);

                    // 2. OTP Verify karne ke liye (Step 2)
                    Route::post('/password/verify-otp', [ProfileApiController::class, 'verifyPasswordOtp']);

                    // 3. Final Password Update karne ke liye (Step 3)
                    Route::post('/password/update', [ProfileApiController::class, 'updatePasswordFinal']);
                });


                // message campaing show and read by user 

                Route::middleware('auth:sanctum')->group(function () {
                    Route::get('/popups/campaigns', [CampaignApiController::class, 'getActiveCampaigns']);
                    Route::post('/popups/mark-read', [CampaignApiController::class, 'markAsRead']);
                });
		
                 // ==================================================================
                // Pop Up Api
                // ==================================================================
                Route::get('/popup/active', [PopupController::class, 'getActivePopup']);

                // Tips  Categories 
                Route::prefix('tips')->group(function () {
                    Route::get('/categories', [CategoryController::class, 'index']);
                }); 

                    // ==========================================
                // Marquee Banners API Controller Methods
                // ==========================================

                Route::middleware('auth:sanctum')->prefix('banners')->group(function () {

                    // Marquee
                    Route::get('marquees', [MarqueeApiController::class, 'index']);      
                    Route::post('marquees', [MarqueeApiController::class, 'store']);     
                    Route::get('marquees/{id}', [MarqueeApiController::class, 'show']);   
                    Route::put('marquees/{id}', [MarqueeApiController::class, 'update']);  
                    Route::delete('marquees/{id}', [MarqueeApiController::class, 'destroy']);
                    Route::post('marquees/{id}/toggle', [MarqueeApiController::class, 'toggle']); 
                });

                
                // ==========================================
                // News & News Categories API Controller Methods
                // ==========================================               

                Route::middleware('auth:sanctum')->prefix('news')->group(function () {
                    // CATEGORIES
                    Route::get('/categories', [NewsCategoryApiController::class, 'index']);
                    Route::post('/categories', [NewsCategoryApiController::class, 'store']);
                    Route::get('/categories/{id}', [NewsCategoryApiController::class, 'show']);
                    Route::put('/categories/{id}', [NewsCategoryApiController::class, 'update']);
                    Route::delete('/categories/{id}', [NewsCategoryApiController::class, 'destroy']);

                    // NEWS
                    Route::get('/', [NewsApiController::class, 'index']);
                    Route::post('/', [NewsApiController::class, 'store']);
                    Route::get('/{id}', [NewsApiController::class, 'show']);
                    Route::put('/{id}', [NewsApiController::class, 'update']);
                    Route::delete('/{id}', [NewsApiController::class, 'destroy']);                                  
                });


                // ==========================================
                // Offer Banners API Controller Methods
                // ==========================================


                Route::middleware('auth:sanctum')->prefix('banners')->group(function () {

                    // ADMIN / CMS
                    Route::get('offers', [OfferBannerApiController::class, 'index']);
                    Route::post('offers', [OfferBannerApiController::class, 'store']);
                    Route::get('offers/{id}', [OfferBannerApiController::class, 'show'])->whereNumber('id');
                    Route::put('offers/{id}', [OfferBannerApiController::class, 'update'])->whereNumber('id');
                    Route::delete('offers/{id}', [OfferBannerApiController::class, 'destroy'])->whereNumber('id');

                    Route::post('offers/{id}/toggle', [OfferBannerApiController::class, 'toggle']);
                });



                // ==========================================
                // Mobile KYC API Controller Methods
                // ==========================================

                Route::middleware('auth:sanctum')->group(function () {

                    Route::post('/kyc/start',  [MobileKycController::class, 'start']);
                    Route::get('/kyc/status', [MobileKycController::class, 'status']);

                });




                // ==========================================
                // MOBILE SUBSCRIPTION API CONTROLLER METHODS
                // ==========================================

                Route::middleware('auth:sanctum')->prefix('mobile/subscription')->group(function () {

                        // 1️⃣ Get active plans (mobile app)
                        Route::get('/plans', [MobileSubscriptionController::class, 'plans']);
	                Route::post('/apply-coupon', [MobileSubscriptionController::class,'applyCoupon'])->middleware('auth:sanctum');
                        // 2️⃣ Create Razorpay order
                        Route::post('/razorpay/initiate', [MobileSubscriptionController::class, 'initiateRazorpay']);
                        // 3️⃣ Verify Razorpay payment (FINAL STEP)
                        Route::post('/razorpay/verify', [MobileSubscriptionController::class, 'verifyRazorpay']);
                        // 4️⃣ Current active subscription
                        Route::get('/current', [MobileSubscriptionController::class, 'currentSubscription']);
                        // 5️⃣ Invoice list
                        Route::get('/invoices', [MobileSubscriptionController::class, 'invoiceList']);
                        // 6️⃣ Invoice PDF download
                        Route::get('/invoice/{id}/download', [MobileSubscriptionController::class, 'downloadInvoice']);
                        Route::get('/coupons', [MobileSubscriptionController::class, 'allCoupons']);

                });



                // Deleted User Data (Delete user Account)
                Route::middleware('auth:sanctum')->delete('/customer/delete-account', [DeleteController::class, 'destroy']);


                // ==================================================================
                // Watchlist Api
                // ==================================================================


                Route::middleware('auth:sanctum')->group(function () {
                    
                    // Watchlist Routes
                    Route::get('/watchlists', [WatchlistController::class, 'index']);
                    Route::post('/watchlists', [WatchlistController::class, 'store']);
                    Route::put('/watchlists/{watchlist}', [WatchlistController::class, 'update']);
                    Route::delete('/watchlists/{watchlist}', [WatchlistController::class, 'destroy']);

                    // Watchlist Scripts Routes
                    Route::post('/watchlists/{watchlist}/scripts', [WatchlistController::class, 'addScript']);
                    Route::delete('/watchlists/scripts/{script}', [WatchlistController::class, 'removeScript']);

                });
		  // ==================================================================
                // Announcement Api 
                // ==================================================================
                Route::prefix('announcements')->group(function () {
                    Route::get('/', [AnnouncementController::class, 'index']);
                    Route::get('/active', [AnnouncementController::class, 'active']);
                    Route::get('/{id}', [AnnouncementController::class, 'show']);
                });
               