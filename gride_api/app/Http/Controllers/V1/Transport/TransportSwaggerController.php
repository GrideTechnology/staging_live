<?php

namespace App\Http\Controllers\V1\Transport;

use App\Http\Controllers\Controller;

class TransportSwaggerController extends Controller
{

	/* *********************************************************************
	*	AVAILABLE SERVICES
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/transport/services",
	*	operationId="apiv1.user.transport.services",
	*	tags={"Transport"},
	*	@OA\Parameter(
	*		name="type",
	*		in="query",
	*		description="Ride Type ID",
	*		required=true,
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Parameter(
	*		name="latitude",
	*		in="query",
	*		description="Current latitude of the user",
	*		required=true,
	*		@OA\Schema(type="string", example="13.0389694")
	*	),
	*	@OA\Parameter(
	*		name="longitude",
	*		in="query",
	*		description="Current longitude of the user",
	*		required=true,
	*		@OA\Schema(type="string", example="80.2095246")
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	ESTIMATE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/transport/estimate",
	*	operationId="apiv1.user.transport.estimate",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/TransportEstimateInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/TransportEstimate")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="TransportEstimate", 
	*	required={"s_latitude", "s_longitude", "service_type", "d_latitude", "d_longitude"}, 
	*	@OA\Property(property="s_latitude", type="string",  example="13.0389694" ),
	*	@OA\Property(property="s_longitude", type="string", example="80.2095246" ),
	*	@OA\Property(property="service_type", type="string" ),
	*	@OA\Property(property="d_latitude", type="string",  example="13.0102357"  ),
	*	@OA\Property(property="d_longitude", type="string",  example="80.2156510"))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="TransportEstimateInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/TransportEstimate"),
	*		@OA\Schema(
	*			@OA\Property(property="s_latitude", type="string"),
	*			@OA\Property(property="s_longitude", type="integer"),
	*			@OA\Property(property="service_type", type="integer"),
	*			@OA\Property(property="d_latitude", type="string"),
	*			@OA\Property(property="d_longitude", type="string")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	CREATE RIDE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/transport/send/request",
	*	operationId="apiv1.user.transport.send.request",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="Create Request",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/TransportCreateRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/TransportCreateRequest")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="TransportCreateRequest", 
	*	required={"s_latitude", "s_longitude", "s_address", "service_type", "d_latitude", "d_longitude", "d_address", "payment_mode", "ride_type_id"}, 
	*	@OA\Property(property="s_latitude", type="string", example="13.0389694" ),
	*	@OA\Property(property="s_longitude", type="string", example="80.2095246" ),
	*	@OA\Property(property="s_address", type="string", example="Chennai" ),
	*	@OA\Property(property="service_type", type="string"),
	*	@OA\Property(property="d_latitude", type="string", example="13.0102357"  ),
	*	@OA\Property(property="d_longitude", type="string", example="80.2156510"),
	*	@OA\Property(property="d_address", type="string", example="Trichy" ),
	*	@OA\Property(property="payment_mode", type="string", example="CASH", enum={"CASH", "CARD"} ),
	*	@OA\Property(property="use_wallet", type="integer"),
	*	@OA\Property(property="schedule_date", type="string"),
	*	@OA\Property(property="schedule_time", type="string" ),
	*	@OA\Property(property="someone", type="string" ),
	*	@OA\Property(property="someone_email", type="string"),
	*	@OA\Property(property="someone_mobile", type="integer" ),
	*	@OA\Property(property="wheel_chair", type="integer" ),
	*	@OA\Property(property="child_seat", type="integer" ),
	*	@OA\Property(property="card_id", type="string"),
	*	@OA\Property(property="ride_type_id", type="integer" ),
	*	@OA\Property(property="promocode_id", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="TransportCreateRequestInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/TransportCreateRequest"),
	*		@OA\Schema(
	*			@OA\Property(property="s_latitude", type="string"),
	*			@OA\Property(property="s_longitude", type="integer"),
	*			@OA\Property(property="s_address", type="integer"),
	*			@OA\Property(property="service_type", type="integer"),
	*			@OA\Property(property="d_latitude", type="string"),
	*			@OA\Property(property="d_longitude", type="string"),
	*			@OA\Property(property="d_address", type="string"),
	*			@OA\Property(property="payment_mode", type="string"),
	*			@OA\Property(property="use_wallet", type="integer"),
	*			@OA\Property(property="schedule_date", type="string"),
	*			@OA\Property(property="schedule_time", type="string"),
	*			@OA\Property(property="someone", type="string"),
	*			@OA\Property(property="someone_email", type="string"),
	*			@OA\Property(property="someone_mobile", type="integer"),
	*			@OA\Property(property="wheel_chair", type="integer"),
	*			@OA\Property(property="child_seat", type="integer"),
	*			@OA\Property(property="card_id", type="string"),
	*			@OA\Property(property="ride_type_id", type="integer"),
	*			@OA\Property(property="promocode_id", type="string")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	CHECK RIDE STATUS
	**********************************************************************/


	/**
	*@OA\Get(
	*	path="/api/v1/user/transport/check/request",
	*	operationId="apiv1.user.transport.check.request",
	*	tags={"Transport"},
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	CHECK RIDE STATUS BY ID
	**********************************************************************/


	/**
	*@OA\Get(
	*	path="/api/v1/user/transport/request/{id}",
	*	operationId="apiv1.user.transport.request.id",
	*	tags={"Transport"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="ride request id",
	*		required=true,
	*		@OA\Schema(type="integer")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/



	/* *********************************************************************
	*	CANCEL RIDE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/transport/cancel/request",
	*	operationId="apiv1.user.transport.cancel.request",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/UserTransportCancelRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UserTransportCancelRequest")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserTransportCancelRequest", 
	*	required={"id"}, 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="reason", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserTransportCancelRequestInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserTransportCancelRequest"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="reason", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	PAYMENT
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/transport/payment",
	*	operationId="apiv1.user.transport.payment",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/UserTransportPaymentInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UserTransportPayment")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserTransportPayment", 
	*	required={"id"}, 
	*	@OA\Property(property="id", type="integer" ),
	*	@OA\Property(property="tips", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserTransportPaymentInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserTransportPayment"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer"),
	*			@OA\Property(property="tips", type="string")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	UPDATE PAYMENT METHOD
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/transport/update/payment",
	*	operationId="apiv1.user.transport.update.payment",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/TransportUpdatePaymentInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/TransportUpdatePayment")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="TransportUpdatePayment", 
	*	required={"id", "payment_mode"}, 
	*	@OA\Property(property="id", type="string", example="13.0389694" ),
	*	@OA\Property(property="payment_mode", type="string", example="CARD", enum={"CASH", "CARD"}  ),
	*	@OA\Property(property="card_id", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="TransportUpdatePaymentInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/TransportUpdatePayment"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="payment_mode", type="string"),
	*			@OA\Property(property="card_id", type="string")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	RATING
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/transport/rate",
	*	operationId="apiv1.user.transport.rate",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/UserTransportRatingInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UserTransportRating")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserTransportRating", 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="rating", type="string", example="5" ),
	*	@OA\Property(property="comment", type="string", example="Test" ),
	*	@OA\Property(property="admin_service", type="string", example="TRANSPORT", enum={"TRANSPORT", "ORDER","SERVICE"}  ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserTransportRatingInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserTransportRating"),
	*		@OA\Schema(
	*			required={"id", "rating", "admin_service"}, 
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="rating", type="integer"),
	*			@OA\Property(property="comment", type="string"),
	*			@OA\Property(property="admin_service", type="string" )
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	EXTEND TRIP
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/transport/extend/trip",
	*	operationId="apiv1.user.transport.extend",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/TransportExtendInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/TransportExtend")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="TransportExtend", 
	*	required={"id", "latitude", "longitude", "address"}, 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="latitude", type="string", example="13.0389694" ),
	*	@OA\Property(property="longitude", type="string", example="80.2095246" ),
	*	@OA\Property(property="address", type="string", example="Chennai"  ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="TransportExtendInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/TransportExtend"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="latitude", type="integer"),
	*			@OA\Property(property="longitude", type="integer"),
	*			@OA\Property(property="address", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	CHECK STATUS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/check/ride/request",
	*	operationId="apiv1.provider.transport.check.request",
	*	tags={"Transport"},
	*	@OA\Parameter(
	*		name="latitude",
	*		in="query",
	*		description="Provider Current Latitude",
	*		required=true,
	*		@OA\Schema(type="string", example="13.0389694")),
	*	@OA\Parameter(
	*		name="longitude",
	*		in="query",
	*		description="Provider Current Longitude",
	*		required=true,
	*		@OA\Schema(type="string", example="80.2095246")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	CANCEL RIDE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/cancel/ride/request",
	*	operationId="apiv1.provider.cancel.ride.request",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="Cancel Ride",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderCancelRideInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderCancelRide")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderCancelRide", 
	*	required={"id", "admin_service", "reason"}, 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="admin_service", type="string", example="TRANSPORT", enum={"TRANSPORT", "ORDER","SERVICE"} ),
	*	@OA\Property(property="reason", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderCancelRideInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderCancelRide"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer"),
	*			@OA\Property(property="admin_service", type="string"),
	*			@OA\Property(property="reason", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	UPDATE RIDE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/update/ride/request",
	*	operationId="apiv1.provider.update.ride.request",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="application/json",
	*				@OA\Schema(ref="#/components/schemas/UpdateRideRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UpdateRideRequest")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UpdateRideRequest", 
	*	required={"_method", "id", "status"}, 
	*	@OA\Property(property="_method", type="string", default="PATCH", example="PATCH" ),
	*	@OA\Property(property="id", type="string", example="" ),
	*	@OA\Property(property="status", type="string", example="ACCEPTED", enum={"ACCEPTED" , "STARTED", "ARRIVED", "PICKEDUP", "DROPPED", "COMPLETED"} ),
	*	@OA\Property(property="otp", type="string", example="", description="During PICKEDUP"  ),
	*	@OA\Property(property="d_latitude", type="string", example="", description="During DROPPED - User Completes Ride Before Destination"  ),
	*	@OA\Property(property="d_longitude", type="string", example="", description="During DROPPED - User Completes Ride Before Destination"  ),
	*	@OA\Property(property="d_address", type="string", example="", description="During DROPPED - User Completes Ride Before Destination"  ),
	*	@OA\Property(property="latitude", type="string", example="", description="During DROPPED with Location Points" ),
	*	@OA\Property(property="longitude", type="string", example="", description="During DROPPED with Location Points"  ),
	*	@OA\Property(property="distance", type="string", example="", description="During DROPPED with Location Points"  ),
	*	@OA\Property(property="location_points", type="array", description="During DROPPED",
	*			@OA\Items(
	*				@OA\Property(property="lat", type="string", example="13.0389694"),
	*				@OA\Property(property="lng", type="string", example="80.2095246")
	*			)  ),
	*	@OA\Property(property="toll_price", type="string", example="", description="During DROPPED"  ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UpdateRideRequestInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UpdateRideRequest"),
	*		@OA\Schema(
	*			@OA\Property(property="_method", type="string"),
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="status", type="string"),
	*			@OA\Property(property="otp", type="string"),
	*			@OA\Property(property="d_latitude", type="string"),
	*			@OA\Property(property="d_longitude", type="string"),
	*			@OA\Property(property="d_address", type="string"),
	*			@OA\Property(property="latitude", type="string"),
	*			@OA\Property(property="longitude", type="string"),
	*			@OA\Property(property="distance", type="string"),
	*			@OA\Property(property="location_points", type="array",
	*			@OA\Items(
	*				@OA\Property(property="lat", type="string"),
	*				@OA\Property(property="lng", type="string"))
	*			),
	*			@OA\Property(property="toll_price", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	WAITING
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/waiting",
	*	operationId="apiv1.provider.waiting",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderWaitingInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderWaiting")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderWaiting", 
	*	required={"id"}, 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="status", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderWaitingInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderWaiting"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer"),
	*			@OA\Property(property="status", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	PAYMENT
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/transport/payment",
	*	operationId="apiv1.provider.transport.payment",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderPaymentInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderPayment")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderPayment", 
	*	required={"id"}, 
	*	@OA\Property(property="id", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderPaymentInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderPayment"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer")
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	RATING
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/rate/ride",
	*	operationId="apiv1.provider.rate.ride",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderTransportRatingInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderTransportRating")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderTransportRating", 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="rating", type="string", example="5" ),
	*	@OA\Property(property="comment", type="string", example="Test" ),
	*	@OA\Property(property="admin_service", type="string", example="TRANSPORT", enum={"TRANSPORT", "ORDER","SERVICE"}  ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderTransportRatingInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderTransportRating"),
	*		@OA\Schema(
	*			required={"id", "rating", "admin_service"}, 
	*			@OA\Property(property="id", type="string"),
	*			@OA\Property(property="rating", type="integer"),
	*			@OA\Property(property="comment", type="string"),
	*			@OA\Property(property="admin_service", type="string" )
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	USER TRIPS HISTORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/trips-history/transport?",
	*	operationId="api.v1.user.trips.history.transport",
	*	tags={"Transport"},
	*	@OA\Parameter(
	*		name="limit",
	*		in="query",
	*		description="limit",
	*		required=false,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Parameter(
	*		name="offset",
	*		in="query",
	*		description="offset",
	*		required=false,
	*		@OA\Schema(type="integer", example="0")),
	*	@OA\Parameter(
	*		name="type",
	*		in="query",
	*		description="past / current / history . if past, 'CANCELLED','COMPLETED'. if history, 'SCHEDULED' else all current requests.",
	*		required=false,
	*		@OA\Schema(type="string", example="past")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Transport ride list history of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER TRIPS HISTORY DETAIL VIEW
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/trips-history/transport/{id}",
	*	operationId="api.v1.user.trips.history.transport.id",
	*	tags={"Transport"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="ride request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular ride history detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	
	/* *********************************************************************
	*	PROVIDER TRIPS HISTORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/history/transport?",
	*	operationId="api.v1.provider.history.transport",
	*	tags={"Transport"},
	*	@OA\Parameter(
	*		name="limit",
	*		in="query",
	*		description="limit",
	*		required=false,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Parameter(
	*		name="offset",
	*		in="query",
	*		description="offset",
	*		required=false,
	*		@OA\Schema(type="integer", example="0")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Transport ride list history of user",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	PROVIDER TRIPS HISTORY DETAIL VIEW
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/history/transport/{id}",
	*	operationId="api.v1.provider.history.transport.id",
	*	tags={"Transport"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="ride request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular ride history detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	/* *********************************************************************
	*	USER DISPUTE SAVE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/ride/dispute",
	*	operationId="api.v1.user.ride.dispute",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="User Ride Dispute",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/UserTransportDisputeInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Saved Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/UserTransportDispute")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserTransportDispute", 
	*	@OA\Property(property="id", type="integer",example=1 ),
	*	@OA\Property(property="dispute_type", type="string", example="user" ),
	*	@OA\Property(property="user_id", type="integer", example=1 ),
	*	@OA\Property(property="provider_id", type="integer", example=1 ),
	*	@OA\Property(property="dispute_name", type="string", example="Not Interested" ),
	*	@OA\Property(property="comments", type="string", example="No Response" ))
	*	
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserTransportDisputeInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserTransportDispute"),
	*		@OA\Schema(
	*			required={"id", "dispute_type", "user_id","provider_id","dispute_name"}, 
	*			@OA\Property(property="id", type="integer",description="ride request id"),
	*			@OA\Property(property="dispute_type", type="string"),
	*           @OA\Property(property="user_id", type="integer" ),
	*           @OA\Property(property="provider_id", type="integer" ),
	*           @OA\Property(property="dispute_name", type="string" ),
	*			@OA\Property(property="comments", type="string")
	*			
	*		)
	*	}
	*)
	*/

	/* *********************************************************************
	*	PROVIDER DISPUTE SAVE
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/history-dispute/transport",
	*	operationId="api.v1.provider.ride.dispute",
	*	tags={"Transport"},
	*	@OA\RequestBody(
	*		description="Provider Ride Dispute",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderTransportDisputeInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Saved Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderTransportDispute")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderTransportDispute", 
	*	@OA\Property(property="id", type="integer",example=1 ),
	*	@OA\Property(property="dispute_type", type="string", example="provider" ),
	*	@OA\Property(property="user_id", type="integer", example=1 ),
	*	@OA\Property(property="provider_id", type="integer", example=1 ),
	*	@OA\Property(property="dispute_name", type="string", example="Not Interested" ),
	*	@OA\Property(property="comments", type="string", example="No Response" ))
	*	
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderTransportDisputeInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderTransportDispute"),
	*		@OA\Schema(
	*			required={"id", "dispute_type", "user_id","provider_id","dispute_name"}, 
	*			@OA\Property(property="id", type="integer",description="ride request id"),
	*			@OA\Property(property="dispute_type", type="string"),
	*           @OA\Property(property="user_id", type="integer" ),
	*           @OA\Property(property="provider_id", type="integer" ),
	*           @OA\Property(property="dispute_name", type="string" ),
	*			@OA\Property(property="comments", type="string")
	*			
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	USER DISPUTE STATUS DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/ride/disputestatus/{id}",
	*	operationId="api.v1.user.ride.disputestatus.id",
	*	tags={"Transport"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="transport request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular transport Dispute status detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/

	/* *********************************************************************
	*	PROVIDER DISPUTE STATUS DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/ride/disputestatus/{id}",
	*	operationId="api.v1.provider.ride.disputestatus.id",
	*	tags={"Transport"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="transport request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular transport Dispute detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


	
	
}