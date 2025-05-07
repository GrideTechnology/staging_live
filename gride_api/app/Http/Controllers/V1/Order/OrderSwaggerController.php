<?php

namespace App\Http\Controllers\V1\Order;

use App\Http\Controllers\Controller;

class OrderSwaggerController extends Controller
{



	/* *********************************************************************
	*	CUISINES
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/store/cusines/{id}",
	*	operationId="apiv1.user.store.cusines",
	*	tags={"Order"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Type ID",
	*		required=true,
	*		@OA\Schema(type="integer")
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
	*	SHOPS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/store/list/{id}",
	*	operationId="apiv1.user.store.list",
	*	tags={"Order"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Type ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Parameter(
	*		name="filter",
	*		in="query",
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Parameter(
	*		name="qfilter",
	*		in="query",
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Parameter(
	*		name="latitude",
	*		in="query",
	*		@OA\Schema(type="string", example="13.0389694")
	*	),
	*	@OA\Parameter(
	*		name="longitude",
	*		in="query",
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
	*	SHOP DETAIL
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/store/details/{id}",
	*	operationId="apiv1.user.store.list",
	*	tags={"Order"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Parameter(
	*		name="filter",
	*		in="query",
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Parameter(
	*		name="qfilter",
	*		in="query",
	*		@OA\Schema(type="string", enum={"non-veg", "pure-veg", "discount"})
	*	),
	*	@OA\Parameter(
	*		name="search",
	*		in="query",
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Parameter(
	*		name="latitude",
	*		in="query",
	*		@OA\Schema(type="string", example="13.0389694")
	*	),
	*	@OA\Parameter(
	*		name="longitude",
	*		in="query",
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
	*	CHECK REQUEST
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/store/check/request",
	*	operationId="apiv1.user.store.check",
	*	tags={"Order"},
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
	*	SHOW ADDONS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/store/show-addons/{id}",
	*	operationId="apiv1.user.store.show.addons",
	*	tags={"Order"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Item ID",
	*		required=true,
	*		@OA\Schema(type="integer")
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
	*	USER ADDRESS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/store/useraddress",
	*	operationId="apiv1.user.store.useraddress",
	*	tags={"Order"},
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
	*	ADD TO CART
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/store/addcart",
	*	operationId="apiv1.user.store.addcart",
	*	tags={"Order"},
	*	description="Add to Cart",
	*	@OA\RequestBody(
	*		description="Add to Cart",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/AddCartInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/AddCart")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="AddCart", 
	*	required={"item_id", "qty", "repeat", "customize"},
	*	@OA\Property(property="item_id", type="string"),
	*	@OA\Property(property="cart_id", type="string"),
	*	@OA\Property(property="qty", type="string"),
	*	@OA\Property(property="addons", type="string"),
	*	@OA\Property(property="repeat", type="boolean"),
	*	@OA\Property(property="customize", type="boolean"))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="AddCartInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/AddCart"),
	*		@OA\Schema(
	*			@OA\Property(property="item_id", type="integer",description="if account_type is mobile, username is mobile number. If account_type is email, username is email id."),
	*			@OA\Property(property="cart_id", type="integer"),
	*			@OA\Property(property="qty", type="float", description="Send cart id if item already exists and add or minus quantity" ),
	*			@OA\Property(property="addons", type="string"),
	*			@OA\Property(property="action", type="string",  description="whenever add or edit addons give action as *addnew* "))
	*	}
	*)
	*/

	/* *********************************************************************
	*	CART LIST
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/user/store/cartlist",
	*	operationId="apiv1.user.store.cartlist",
	*	tags={"Order"},
	*	@OA\Parameter(
	*		name="promocode_id",
	*		in="query",
	*		description="Promocode ID",
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Parameter(
	*		name="wallet",
	*		in="query",
	*		description="Wallet",
	*		required=true,
	*		@OA\Schema(type="string")
	*	),
	*	@OA\Parameter(
	*		name="user_address_id",
	*		in="query",
	*		description="User Address ID",
	*		required=true,
	*		@OA\Schema(type="string")
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
	*	CHECKOUT
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/store/checkout",
	*	operationId="apiv1.user.store.checkout",
	*	tags={"Order"},
	*	description="Checkout",
	*	@OA\RequestBody(
	*		description="Checkout",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/CheckoutInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/Checkout")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="Checkout", 
	*	required={"payment_mode", "user_address_id"},
	*	@OA\Property(property="promocode_id", type="integer" ),
	*	@OA\Property(property="wallet", type="string" ),
	*	@OA\Property(property="payment_mode", type="string", example="CASH", enum={"CASH", "CARD"}),
	*	@OA\Property(property="user_address_id", type="integer" ),
	*	@OA\Property(property="delivery_date", type="string" ),
	*	@OA\Property(property="order_type", type="string" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="CheckoutInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/Checkout"),
	*		@OA\Schema(
	*			@OA\Property(property="promocode_id", type="integer"),
	*			@OA\Property(property="wallet", type="string"),
	*			@OA\Property(property="payment_mode", type="string" ),
	*			@OA\Property(property="user_address_id", type="string"),
	*			@OA\Property(property="delivery_date", type="string"),
	*			@OA\Property(property="order_type", type="string"))
	*	}
	*)
	*/

	/* *********************************************************************
	*	CANCEL ORDER
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/order/cancel/request",
	*	operationId="apiv1.user.order.cancel.request",
	*	tags={"Order"},
	*	description="Add to Cart",
	*	@OA\RequestBody(
	*		description="Add to Cart",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/UserCancelOrderInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserCancelOrder")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserCancelOrder", 
	*	required={"id", "cancel_reason"},
	*	@OA\Property(property="id", type="integer" ),
	*	@OA\Property(property="cancel_reason", type="string" ),
	*	@OA\Property(property="cancel_reason_opt", type="string"))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserCancelOrderInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserCancelOrder"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer",description="order request id"),
	*			@OA\Property(property="cancel_reason", type="string", description="reasons list select dropdown value"),
	*			@OA\Property(property="cancel_reason_opt", type="string", description="optional. when choosing 'others' in cancel_reason this box will appear" ))
	*	}
	*)
	*/


	/* *********************************************************************
	*	RATING
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/user/store/order/rating",
	*	operationId="apiv1.user.store.order.rating",
	*	tags={"Order"},
	*	description="Add to Cart",
	*	@OA\RequestBody(
	*		description="Add to Cart",
	*		@OA\MediaType(
	* 			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/UserOrderRatingInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns settings for the application",
	*		@OA\JsonContent(ref="#/components/schemas/UserOrderRating")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserOrderRating", 
	*	required={"request_id", "shopid", "rating", "shoprating"},
	*	@OA\Property(property="request_id", type="integer" ),
	*	@OA\Property(property="rating", type="integer" ),
	*	@OA\Property(property="shopid", type="integer" ),
	*	@OA\Property(property="shoprating", type="integer" ),
	*	@OA\Property(property="comment", type="string"))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="UserOrderRatingInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserOrderRating"),
	*		@OA\Schema(
	*			@OA\Property(property="request_id", type="integer",description="order request id"),
	*			@OA\Property(property="rating", type="string", description="delivery boy rating"),
	*			@OA\Property(property="shoprating", type="string", description="shop rating"),
	*			@OA\Property(property="shopid", type="integer", description="shop Id"),
	*			@OA\Property(property="comment", type="string", description="shop rating" ))
	*	}
	*)
	*/

	/* *********************************************************************
	*	CHECK STATUS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/provider/check/order/request",
	*	operationId="apiv1.provider.order.check.request",
	*	tags={"Order"},
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
	*	CANCEL ORDER (CREATE DISPUTE)
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/cancel/order/request",
	*	operationId="apiv1.provider.cancel.order.request",
	*	tags={"Order"},
	*	@OA\RequestBody(
	*		description="Create Dispute",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderCancelOrderInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderCancelOrder")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderCancelOrder", 
	*	required={"id", "reason"}, 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="reason", type="string" ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderCancelOrderInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderCancelOrder"),
	*		@OA\Schema(
	*			@OA\Property(property="id", type="integer"),
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
	*	path="/api/v1/provider/update/order/request",
	*	operationId="apiv1.provider.update.order.request",
	*	tags={"Order"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/UpdateOrderRequestInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/UpdateOrderRequest")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UpdateOrderRequest", 
	*	required={"_method", "id", "status"}, 
	*	@OA\Property(property="_method", type="string", default="PATCH" ),
	*	@OA\Property(property="id", type="string", example="" ),
	*	@OA\Property(property="status", type="string", example="STARTED", enum={"ORDERED", "RECEIVED", "CANCELLED", "ASSIGNED", "PROCESSING", "REACHED", "PICKEDUP", "ARRIVED", "DELIVERED", "COMPLETED", "SEARCHING", "STORECANCELLED"} ),
	*	@OA\Property(property="otp", type="string", example="", description="During DELIVERED"  ),
	*	@OA\Property(property="d_latitude", type="string", example="", description="During DROPPED - User Completes Ride Before Destination"  ),
	*	@OA\Property(property="d_longitude", type="string", example="", description="During DROPPED - User Completes Ride Before Destination"  ),
	*	@OA\Property(property="d_address", type="string", example="", description="During DROPPED - User Completes Ride Before Destination"  ),
	*	@OA\Property(property="latitude", type="string", example="", description="During DROPPED with Location Points" ),
	*	@OA\Property(property="longitude", type="string", example="", description="During DROPPED with Location Points"  ),
	*	@OA\Property(property="distance", type="string", example="", description="During DROPPED with Location Points"  ),
	*	@OA\Property(property="toll_price", type="string", example="", description="During DROPPED"  ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UpdateOrderRequestInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UpdateOrderRequest"),
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
	*			@OA\Property(property="toll_price", type="string")
	*		)
	*	}
	*)
	*/


	/* *********************************************************************
	*	RATING
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/provider/rate/order",
	*	operationId="apiv1.provider.rate.order",
	*	tags={"Order"},
	*	@OA\RequestBody(
	*		description="User Login",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderOrderRatingInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns available services, providers and promocodes",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderOrderRating")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderOrderRating", 
	*	@OA\Property(property="id", type="string" ),
	*	@OA\Property(property="rating", type="string", example="5" ),
	*	@OA\Property(property="comment", type="string", example="Test" ),
	*	@OA\Property(property="admin_service", type="string", example="ORDER", enum={"TRANSPORT", "ORDER","SERVICE"}  ))
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderOrderRatingInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderOrderRating"),
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
	*	path="/api/v1/user/trips-history/order?",
	*	operationId="api.v1.user.trips.history.order",
	*	tags={"Order"},
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
	*		description="Returns Transport Service list history of user",
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
	*	path="/api/v1/user/trips-history/order/{id}",
	*	operationId="api.v1.user.trips.history.order.id",
	*	tags={"Order"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Order request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular Service history detail",
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
	*	path="/api/v1/provider/history/order?",
	*	operationId="api.v1.provider.history.order",
	*	tags={"Order"},
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
	*		description="Returns order list history of user",
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
	*	path="/api/v1/provider/history/order/{id}",
	*	operationId="api.v1.provider.history.order.id",
	*	tags={"Order"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="order request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular order history detail",
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
	*	path="/api/v1/user/order/dispute",
	*	operationId="api.v1.user.order.dispute",
	*	tags={"Order"},
	*	@OA\RequestBody(
	*		description="User Order Dispute",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/UserOrderDisputeInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Saved Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/UserOrderDispute")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"User": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="UserOrderDispute", 
	*	@OA\Property(property="id", type="integer",example=1 ),
	*	@OA\Property(property="dispute_type", type="string", example="user" ),
	*	@OA\Property(property="user_id", type="integer", example=1 ),
	*	@OA\Property(property="provider_id", type="integer", example=1 ),
	*	@OA\Property(property="dispute_name", type="string", example="Not Interested" ),
	*	@OA\Property(property="store_id", type="integer", example=1 ),
	*	@OA\Property(property="comments", type="string", example="No Response" ))
	*	
	*
	*/
	/**
	*@OA\Schema(
	*	schema="UserOrderDisputeInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/UserOrderDispute"),
	*		@OA\Schema(
	*		required={"id", "dispute_type", "user_id","provider_id","dispute_name","store_id"}, 
	*			@OA\Property(property="id", type="integer",description="Store Order id"),
	*			@OA\Property(property="dispute_type", type="string"),
	*           @OA\Property(property="user_id", type="integer" ),
	*           @OA\Property(property="store_id", type="integer" ),
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
	*	path="/api/v1/provider/history-dispute/order",
	*	operationId="api.v1.provider.order.dispute",
	*	tags={"Order"},
	*	@OA\RequestBody(
	*		description="Provider Order Dispute",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*				@OA\Schema(ref="#/components/schemas/ProviderOrderDisputeInput"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Saved Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/ProviderOrderDispute")
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/
	/**
	*@OA\Schema(schema="ProviderOrderDispute", 
	*	@OA\Property(property="id", type="integer",example=1 ),
	*	@OA\Property(property="dispute_type", type="string", example="provider" ),
	*	@OA\Property(property="user_id", type="integer", example=1 ),
	*	@OA\Property(property="provider_id", type="integer", example=1 ),
	*	@OA\Property(property="dispute_name", type="string", example="Not Interested" ),
	*    @OA\Property(property="store_id", type="integer", example=1 ),
	*	@OA\Property(property="comments", type="string", example="No Response" ))
	*	
	*
	*/
	/**
	*@OA\Schema(
	*	schema="ProviderOrderDisputeInput",
	*	type="object",
	*	allOf={
	*		@OA\Schema(ref="#/components/schemas/ProviderOrderDispute"),
	*		@OA\Schema(
	*			required={"id", "dispute_type", "user_id","provider_id","dispute_name"}, 
	*			@OA\Property(property="id", type="integer",description="Store Order id"),
	*			@OA\Property(property="dispute_type", type="string"),
	*           @OA\Property(property="user_id", type="integer" ),
	*           @OA\Property(property="provider_id", type="integer" ),
	*           @OA\Property(property="dispute_name", type="string" ),
	*           @OA\Property(property="store_id", type="integer" ),
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
	*	path="/api/v1/user/order/disputestatus/{id}",
	*	operationId="api.v1.user.order.disputestatus.id",
	*	tags={"Order"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="order request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular order Dispute detail",
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
	*	path="/api/v1/provider/order/disputestatus/{id}",
	*	operationId="api.v1.provider.order.disputestatus.id",
	*	tags={"Order"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="order request id",
	*		required=true,
	*		@OA\Schema(type="integer", example="10")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns particular order Dispute detail",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Provider": {}} },
	* )
	*/


}