<?php

use Illuminate\Database\Seeder;

class UpdateSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Configuration for live
        //"https:\/\/api.gridetech.com\/images\/common\/logo.png",
        //  $data = '{
        //     "site": {
        //         "site_title": "GoX",
        //         "contact_number": [{
        //             "number": "911"
        //         }],
        //         "language": [{
        //             "name": "English",
        //             "key": "en"
        //         }, {
        //             "name": "Arabic",
        //             "key": "ar"
        //         }],
        //         "country": "231",
        //         "city": "48294",
        //         "contact_email": "admin@gox.com",
        //         "sos_number": "911",
        //         "site_copyright": "&copy; Copyrights 2019 All Rights Reserved.",
        //         "store_link_android_user": "",
        //         "store_link_android_provider": "",
        //         "store_link_ios_user": "",
        //         "store_link_ios_provider": "",
        //         "store_facebook_link": "",
        //         "store_twitter_link": "",
        //         "store_instagram_link": "",
        //         "store_youtube_link": "",
        //         "store_linkedin_link": "",
        //         "image": "",
        //         "site_logo": "https:\/\/api.gridetech.com\/storage\/app\/public\/1\/site\/site_logo.png",
        //         "site_icon": "https:\/\/api.gridetech.com\/images\/common\/favicon.ico",
        //         "browser_key": "AIzaSyCqVq20mL-pM0OaVq-FYOiQmuNmGEdp34Q",
        //         "server_key": "AIzaSyBUAqh3HZlWcLu8aN-daT6BwMgagga-C7U",
        //         "android_key": "AIzaSyBUAqh3HZlWcLu8aN-daT6BwMgagga-C7U",
        //         "ios_key": "",
        //         "social_login": "0",
        //         "facebook_app_version": "",
        //         "facebook_app_id": "",
        //         "facebook_app_secret": "",
        //         "google_client_id": "",
        //         "environment": "production",
        //         "ios_push_password": "appoets123$",
        //         "android_push_key": "AAAAiLO3YWY:APA91bHuPa6lr94gTaKZ0w7WcJ3IYNsTNSGctH024Luh1i3LJ8PphG6OunXYySps4MEDeiSE-1R2iDPiPurx-JX58iZsGug5o9NzDBMo7xp12fqBSNxO7hsPbef1t1yCd3TqDDYli1Hg",
        //         "user_pem": "",
        //         "send_email": "1",
        //         "mail_driver": "SMTP",
        //         "mail_port": "587",
        //         "mail_host": "smtp.gmail.com",
        //         "mail_username": "",
        //         "mail_password": "",
        //         "mail_from_address": "admin@gox.com",
        //         "mail_from_name": "GoX",
        //         "mail_encryption": "tls",
        //         "mail_domain": "",
        //         "mail_secret": "",
        //         "send_sms": "0",
        //         "sms_driver": "TWILIO",
        //         "sms_provider": "TWILIO",
        //         "sms_account_sid": "",
        //         "sms_auth_token": "ecacfd653b46555817f244d4d312f739",
        //         "sms_from_number": "+14153389659",
        //         "twilio_key": "",
        //         "twilio_secret": "4ugY9RrGmV15SxS3jEywTInYoMjJzmVl",
        //         "app_sid": "AP200f9ead73e9de96641cd9b4176728b4",
        //         "referral": "1",
        //         "referral_count": "5",
        //         "referral_amount": "50.00",
        //         "distance": "Miles",
        //         "currency": "$",
        //         "round_decimal": "2",
        //         "cash": "",
        //         "card": "",
        //         "stripe_secret_key": "",
        //         "stripe_publishable_key": "",
        //         "stripe_currency": "",
        //         "page_privacy": "https:\/\/gridetech.com\/pages\/page_privacy",
        //         "help": "https:\/\/gridetech.com\/pages\/help",
        //         "terms": "https:\/\/gridetech.com\/pages\/terms",
        //         "cancel": "https:\/\/gridetech.com\/pages\/cancel",
        //         "about_us": "https:\/\/gridetech.com\/pages\/about_us",
        //         "legal": "https:\/\/gridetech.com\/pages\/legal",
        //         "faq": "https:\/\/gridetech.com\/pages\/faq",
        //         "provider_pem": "",
        //         "provider_negative_balance": "0",
        //         "user_unique_id": "USR",
        //         "provider_unique_id": "PRV"
        //     },
        //     "transport": {
        //         "ride_otp": "0",
        //         "manual_request": "0",
        //         "broadcast_request": "1",
        //         "provider_search_radius": "200",
        //         "user_select_timeout": "180",
        //         "provider_select_timeout": "60",
        //         "booking_prefix": "TRNX",
        //         "unit_measurement": "Miles",
        //         "destination": "1",
        //         "geofence" : "0"
        //     },
        //     "order": {
        //         "serve_otp": "1",
        //         "manual_request": "0",
        //         "broadcast_request": "1",
        //         "tax_percentage": "",
        //         "commission_percentage": "",
        //         "surge_trigger": "",
        //         "provider_search_radius": "200",
        //         "provider_select_timeout": "60",
        //         "time_left_to_respond": 360,
        //         "surge_percentage": "",
        //         "track_distance": "1",
        //         "booking_prefix": "TRNXF",
        //         "store_search_radius": "200",
        //         "store_response_time": "60",
        //         "store": "1",
        //         "order_otp": "0",
        //         "search_radius": "200",
        //         "response_time": "60",
        //         "max_items_in_order": "2"
        //     },
        //     "service": {
        //         "serve_otp": "0",
        //         "manual_request": "0",
        //         "broadcast_request": "1",
        //         "tax_percentage": "",
        //         "commission_percentage": "",
        //         "surge_trigger": "",
        //         "provider_search_radius": "200",
        //         "provider_select_timeout": "60",
        //         "time_left_to_respond": 60,
        //         "surge_percentage": "",
        //         "track_distance": "1",
        //         "booking_prefix": "TRNXS",
        //         "service": "SER"
        //     },
        //     "payment": [{
        //         "name": "cash",
        //         "status": 0,
        //         "credentials": []
        //     }, {
        //         "name": "card",
        //         "status": 1,
        //         "credentials": [{
        //             "name": "stripe_secret_key",
        //             "value": ""
        //         }, {
        //             "name": "stripe_publishable_key",
        //             "value": ""
        //         }, {
        //             "name": "stripe_currency",
        //             "value": "usd"
        //         }]
        //     }]
        // }';//Client location


        //Configuration for dev6
        $data = '{
            "site": {
                "site_title": "GoX",
                "contact_number": [{
                    "number": "911"
                }],
                "language": [{
                    "name": "English",
                    "key": "en"
                }, {
                    "name": "Arabic",
                    "key": "ar"
                }],
                "country": "231",
                "city": "48294",
                "contact_email": "admin@gox.com",
                "sos_number": "911",
                "site_copyright": "&copy; Copyrights 2019 All Rights Reserved.",
                "store_link_android_user": "",
                "store_link_android_provider": "",
                "store_link_ios_user": "",
                "store_link_ios_provider": "",
                "store_facebook_link": "",
                "store_twitter_link": "",
                "store_instagram_link": "",
                "store_youtube_link": "",
                "store_linkedin_link": "",
                "image": "",
                "site_logo": "http:\/\/dev6.spaceo.in\/project\/grideapp_web\/storage\/app\/public\/1\/site\/site_logo.jpg",
                "site_icon": "https:\/\/dev6.spaceo.in\/project\/grideapp_web\/public\/images\/common\/favicon.ico",
                "browser_key": "AIzaSyCqVq20mL-pM0OaVq-FYOiQmuNmGEdp34Q",
                "server_key": "AIzaSyBUAqh3HZlWcLu8aN-daT6BwMgagga-C7U",
                "android_key": "AIzaSyBUAqh3HZlWcLu8aN-daT6BwMgagga-C7U",
                "ios_key": "",
                "social_login": "0",
                "facebook_app_version": "",
                "facebook_app_id": "",
                "facebook_app_secret": "",
                "google_client_id": "",
                "environment": "development",
                "ios_push_password": "appoets123$",
                "android_push_key": "AAAAiLO3YWY:APA91bHuPa6lr94gTaKZ0w7WcJ3IYNsTNSGctH024Luh1i3LJ8PphG6OunXYySps4MEDeiSE-1R2iDPiPurx-JX58iZsGug5o9NzDBMo7xp12fqBSNxO7hsPbef1t1yCd3TqDDYli1Hg",
                "user_pem": "",
                "send_email": "1",
                "mail_driver": "SMTP",
                "mail_port": "587",
                "mail_host": "smtp.gmail.com",
                "mail_username": "",
                "mail_password": "",
                "mail_from_address": "admin@gox.com",
                "mail_from_name": "GoX",
                "mail_encryption": "tls",
                "mail_domain": "",
                "mail_secret": "",
                "send_sms": "0",
                "sms_driver": "TWILIO",
                "sms_provider": "TWILIO",
                "sms_account_sid": "",
                "sms_auth_token": "ecacfd653b46555817f244d4d312f739",
                "sms_from_number": "+14153389659",
                "twilio_key": "",
                "twilio_secret": "4ugY9RrGmV15SxS3jEywTInYoMjJzmVl",
                "app_sid": "AP200f9ead73e9de96641cd9b4176728b4",
                "referral": "1",
                "referral_count": "5",
                "referral_amount": "50.00",
                "distance": "Miles",
                "currency": "$",
                "round_decimal": "2",
                "cash": "",
                "card": "",
                "stripe_secret_key": "",
                "stripe_publishable_key": "",
                "stripe_currency": "",
                "page_privacy": "https:\/\/dev6.spaceo.in\/project\/grideapp_admin_frontend\/public\/pages\/page_privacy",
                "help": "https:\/\/dev6.spaceo.in\/project\/grideapp_admin_frontend\/public\/pages\/help",
                "terms": "https:\/\/dev6.spaceo.in\/project\/grideapp_admin_frontend\/public\/pages\/terms",
                "cancel": "https:\/\/dev6.spaceo.in\/project\/grideapp_admin_frontend\/public\/pages\/cancel",
                "about_us": "https:\/\/dev6.spaceo.in\/project\/grideapp_admin_frontend\/public\/pages\/about_us",
                "legal": "https:\/\/dev6.spaceo.in\/project\/grideapp_admin_frontend\/public\/pages\/legal",
                "faq": "https:\/\/dev6.spaceo.in\/project\/grideapp_admin_frontend\/public\/pages\/faq",
                "provider_pem": "",
                "provider_negative_balance": "0",
                "user_unique_id": "USR",
                "provider_unique_id": "PRV"
                "credit_ride_limit": "0",
                "unit_measurement": "Miles",
                "store_provider_select_timeout": "60",
                "store_booking_prefix": "TRNXF",
                "backup_db": "COMMON",
                "country_code": "in",
                "adminservice": {
                    "1": "1",
                    "3": "1",
                    "2": "1"
                },
                "date_format": "1"
            },
            "transport": {
                "ride_otp": "0",
                "manual_request": "0",
                "broadcast_request": "1",
                "provider_search_radius": "200",
                "user_select_timeout": "180",
                "provider_select_timeout": "60",
                "booking_prefix": "TRNX",
                "unit_measurement": "Miles",
                "destination": "1",
                "geofence" : "0"
                "credit_ride_limit": "0"
            },
            "order": {
                "serve_otp": "1",
                "manual_request": "0",
                "broadcast_request": "1",
                "tax_percentage": "",
                "commission_percentage": "",
                "surge_trigger": "",
                "provider_search_radius": "200",
                "provider_select_timeout": "60",
                "time_left_to_respond": 360,
                "surge_percentage": "",
                "track_distance": "1",
                "booking_prefix": "TRNXF",
                "store_search_radius": "200",
                "store_response_time": "60",
                "store": "1",
                "order_otp": "0",
                "search_radius": "200",
                "response_time": "60",
                "max_items_in_order": "2"
            },
            "service": {
                "serve_otp": "0",
                "manual_request": "0",
                "broadcast_request": "1",
                "tax_percentage": "",
                "commission_percentage": "",
                "surge_trigger": "",
                "provider_search_radius": "200",
                "provider_select_timeout": "60",
                "time_left_to_respond": 60,
                "surge_percentage": "",
                "track_distance": "1",
                "booking_prefix": "TRNXS",
                "service": "SER"
            },
            "payment": [{
                "name": "cash",
                "status": 0,
                "credentials": []
            }, {
                "name": "card",
                "status": 1,
                "credentials": [{
                    "name": "stripe_secret_key",
                    "value": ""
                }, {
                    "name": "stripe_publishable_key",
                    "value": ""
                }, {
                    "name": "stripe_currency",
                    "value": "usd"
                }]
            }]
        }';

        // echo'<pre>'; print_r($data); exit;
        // $newdata = json_encode($data);                                
        DB::statement("UPDATE `settings` SET `settings_data` = '".$data."' WHERE id = 1");
    }
}
