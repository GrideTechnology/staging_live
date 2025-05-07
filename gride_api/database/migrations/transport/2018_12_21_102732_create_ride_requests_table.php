<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRideRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->create('ride_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->enum('vehicle_type', ['RIDE','DELIVERY']);
            $table->enum('finished_vehicle_type', ['RIDE','DELIVERY','RENTAL','OUTSTATION','OUTSTATION-ROUNDTRIP'])->nullable();
            $table->string('booking_id');
            $table->enum('admin_service', ['TRANSPORT','ORDER','SERVICE'])->nullable(); 
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('provider_id')->nullable();
            $table->unsignedInteger('provider_vehicle_id')->nullable();
            $table->unsignedInteger('provider_service_id')->nullable();
            $table->unsignedInteger('ride_type_id')->nullable();
            $table->unsignedInteger('geofence_id')->nullable();
            $table->unsignedInteger('rental_package_id')->nullable();
            $table->unsignedInteger('ride_delivery_id');
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('promocode_id')->nullable();
            $table->unsignedInteger('company_id');
            $table->enum('status', ['SEARCHING','CANCELLED','ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED','COMPLETED','SCHEDULED'])->nullable();
            $table->enum('cancelled_by', ['NONE','USER','PROVIDER'])->nullable();
            $table->string('cancel_reason')->nullable();
            $table->string('payment_mode')->nullable();
            $table->tinyInteger('paid')->default(0);
            $table->enum('is_track', ['YES','NO']);
            $table->enum('calculator', ['DISTANCE','MIN','HOUR','DISTANCEMIN','DISTANCEHOUR','RENTAL','OUTSTATION','OUTSTATION-ROUNDTRIP']);
            $table->tinyInteger('someone')->default(0);
            $table->double('distance', 10, 2)->nullable();
            $table->string('someone_name')->nullable();
            $table->double('someone_mobile')->nullable();
            $table->text('location_points');
            $table->string('timezone')->nullable();
            $table->string('someone_email')->nullable();
            $table->string('travel_time')->nullable();
            $table->string('s_address')->nullable();
            $table->double('s_latitude', 15, 8);
            $table->double('s_longitude', 15, 8);
            $table->string('d_address')->nullable();
            $table->double('d_latitude', 15, 8);
            $table->double('d_longitude', 15, 8);
            $table->double('track_distance', 10, 2);
            $table->tinyInteger('is_drop_location')->default(1);
            $table->text('destination_log');
            $table->enum('unit', ['KMS','MILES']);
            $table->string('currency')->nullable();
            $table->double('track_latitude', 15, 8);
            $table->double('track_longitude', 15, 8);
            $table->string('otp')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('schedule_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('return_date')->nullable();
            $table->enum('is_scheduled', ['YES', 'NO'])->default('NO');
            $table->enum('request_type', ['AUTO','MANUAL'])->default('AUTO');
            $table->unsignedInteger('peak_hour_id')->nullable();
            $table->tinyInteger('user_rated')->default(0);
            $table->tinyInteger('provider_rated')->default(0);
            $table->tinyInteger('use_wallet')->default(0);
            $table->tinyInteger('surge')->default(0)->nullable();
            $table->longText('route_key')->nullable();
            $table->unsignedInteger('admin_id')->nullable();
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ride_requests');
    }
}
