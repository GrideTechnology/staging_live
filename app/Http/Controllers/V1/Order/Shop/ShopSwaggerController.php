<?php

namespace App\Http\Controllers\V1\Order;

use App\Http\Controllers\Controller;

class ShopSwaggerController extends Controller
{



	/* *********************************************************************
	*	Country List
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/countries",
	*	operationId="apiv1.shop.countries",
	*	tags={"Shop"},
    *	@OA\Response(
	*		response="200",
	*		description="Country Details",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	SHOP DASHBOARD
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/total/storeorder",
	*	operationId="apiv1.shop.total.storeorder",
	*	tags={"Shop"},
    *	@OA\Response(
	*		response="200",
	*		description="DASHBOARD DEATILS",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/







	/* *********************************************************************
	*	CUISINES
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/cuisinelist/{id}", 
	*	operationId="apiv1.shop.cuisinelist",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Type ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns shops Cuisinelist",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	SHOP DETAIL
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/shops/details/{id}",
	*	operationId="apiv1.shop.shops.list",
	*	tags={"Shop"},
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
	*		description="Update Shop Details",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/


	/* *********************************************************************
	*	SHOW SHOP DETAIL
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/shops/{id}",
	*	operationId="apiv1.shop.shops",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store ID",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),

	*	@OA\Response(
	*		response="200",
	*		description="Shop Details",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/


    
	/* *********************************************************************
	*	UPDATE ADDONS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/shops/{id}",
	*	operationId="api.v1.shop.update",
	*	tags={"Shop"},
	*	description="Shop Update",
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="SHOP ID",
	*		required=true,
	*		@OA\Schema(type="integer", example="1")),
	*	@OA\RequestBody(
	*		description="Update SHOP",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/updateshop"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="SHOP Updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/updateshop")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="updateshop", 
	* 	required={"store_name","store_type_id","email","contact_number","store_location","latitude","longitude","store_zipcode","zone_id"}, 
	*	@OA\Property(property="store_name", type="string" ),
	*	@OA\Property(property="status", type="integer" ),
	*	@OA\Property(property="is_veg", type="integer" ),
	*	@OA\Property(property="email", type="string" ),
	*	@OA\Property(property="estimated_delivery_time", type="integer" ),
	*	@OA\Property(property="contact_number", type="string" ),
	*	@OA\Property(property="password", type="string" ),
	*	@OA\Property(property="store_location", type="string" ),
	*	@OA\Property(property="latitude", type="string" ),
	*	@OA\Property(property="longitude", type="string" ),
	*	@OA\Property(property="store_zipcode", type="string" ),
	*	@OA\Property(property="contact_person", type="string" ),
	*	@OA\Property(property="store_packing_charges", type="float" ),
	*	@OA\Property(property="zone_id", type="integer" ),
	*	@OA\Property(property="store_gst", type="integer" ),
	*	@OA\Property(property="offer_min_amount", type="float" ),
	*	@OA\Property(property="offer_percent", type="integer" ),
	*	@OA\Property(property="description", type="text" ),
	*	@OA\Property(property="country_id", type="integer" ),
	*	@OA\Property(property="city_id", type="integer" ),
	*	@OA\Property(property="commission", type="float" ),
	*	@OA\Property(property="country_code", type="integer" ),
	*	@OA\Property(property="picture", type="string" ),
    *	@OA\Property(property="cuisine_id[]", type="array", @OA\Items( type="integer"),example="1"),
	*	@OA\Property(property="_method", type="string", example="PATCH" )),
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="updateshopInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/updateshop"),
	*	@OA\Schema(
	*	@OA\Property(property="store_name", type="string" ),
	*	@OA\Property(property="status", type="integer" ),
	*	@OA\Property(property="is_veg", type="integer" ),
	*	@OA\Property(property="email", type="string" ),
	*	@OA\Property(property="estimated_delivery_time", type="integer" ),
	*	@OA\Property(property="contact_number", type="string" ),
	*	@OA\Property(property="password", type="string" ),
	*	@OA\Property(property="store_location", type="string" ),
	*	@OA\Property(property="latitude", type="string" ),
	*	@OA\Property(property="longitude", type="string" ),
	*	@OA\Property(property="store_zipcode", type="string" ),
	*	@OA\Property(property="contact_person", type="string" ),
	*	@OA\Property(property="store_packing_charges", type="float" ),
	*	@OA\Property(property="zone_id", type="integer" ),
	*	@OA\Property(property="store_gst", type="integer" ),
	*	@OA\Property(property="offer_min_amount", type="float" ),
	*	@OA\Property(property="offer_percent", type="integer" ),
	*	@OA\Property(property="description", type="text" ),
	*	@OA\Property(property="country_id", type="integer" ),
	*	@OA\Property(property="city_id", type="integer" ),
	*	@OA\Property(property="commission", type="float" ),
	*	@OA\Property(property="country_code", type="integer" ),
	*	@OA\Property(property="picture", type="string" ),
    *	@OA\Property(property="cuisine_id[]", type="array", @OA\Items( type="integer"),example="1"),
	*	@OA\Property(property="_method", type="string", example="PATCH" ))
	*	}
	* )
	*/
























    /* *********************************************************************
	*	TABLELIST Category
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/categoryindex/{id}",
	*	operationId="apiv1.shop.category",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	    @OA\Parameter(
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
	*		description="Returns All Category of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	LIST Category
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/categorylist/{id}",
	*	operationId="apiv1.shop.categorylist",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Category Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	
	*	@OA\Response(
	*		response="200",
	*		description="All Categories of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/ 

	/* *********************************************************************
	*	STORE CATEGORY
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/category",
	*	operationId="api.v1.shop.category",
	*	tags={"Shop"},
	*	description="Store Category",
	*	@OA\RequestBody(
	*		description="Store Category",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/storecategory"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Category Changed Successfully",
	*		@OA\JsonContent(ref="#/components/schemas/storecategory")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="storecategory", 
	* 	required={"store_category_name","store_id","store_category_description","picture"}, 
	*	@OA\Property(property="store_category_name", type="string" ),
	*	@OA\Property(property="store_id", type="integer" ),
	*	@OA\Property(property="store_category_description", type="text" ),
	*	@OA\Property(property="picture", type="file" ),
	*	@OA\Property(property="store_category_status", type="interger", example="1" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="storecategoryInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/storecategory"),
	*	@OA\Schema(
	*	@OA\Property(property="store_category_name", type="string" ),
	*	@OA\Property(property="store_id", type="integer" ),
	*	@OA\Property(property="store_category_description", type="text" ),
	*	@OA\Property(property="picture", type="file" ),
	*	@OA\Property(property="store_category_status", type="interger", example="1" ))
	*	}
	* )
	*/

	/* *********************************************************************
	*	SHOW CATEGORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/category/{id}",
	*	operationId="apiv1.shop.category",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Category Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*
	*	@OA\Response(
	*		response="200",
	*		description="Returns All Category of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	UPDATE ADDONS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/category/{id}",
	*	operationId="api.v1.shop.updatecategory",
	*	tags={"Shop"},
	*	description="Category Update",
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Category ID",
	*		required=true,
	*		@OA\Schema(type="integer", example="1")),
	*	@OA\RequestBody(
	*		description="Update Add On",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/updatecategory"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Category Updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/updatecategory")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="updatecategory", 
	* 	required={"store_category_name","store_category_description"}, 
	*	@OA\Property(property="store_category_name", type="string" ),
	*	@OA\Property(property="store_category_description", type="text" ),
	*	@OA\Property(property="_method", type="string", example="PATCH" ),
	*	@OA\Property(property="store_category_status", type="interger", example="1" )),
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="updatecategoryInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/updatecategory"),
	*	@OA\Schema(
	*		@OA\Property(property="store_category_name", type="string"),
	*		@OA\Property(property="store_category_description", type="text"),
	*		@OA\Property(property="_method", type="string"),
	*		@OA\Property(property="store_category_status", type="interger"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	Category Delete 
	**********************************************************************/

	/**
	*@OA\Delete(
	*	path="/api/v1/shop/category/{id}",
	*	operationId="api.v1.shop.deletecategory",
	*	tags={"Shop"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="category id",
	*		required=true,
	*		@OA\Schema(type="integer", example="1")),
	*	@OA\Response(
	*		response="200",
	*		description="category deleted successfully",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	* CATEGORY STATUS UPDATE
 	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/category/{id}/updateStatus",
	*	operationId="apiv1.shop.updatecategoryStatus",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Category Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	
	*	@OA\Response(
	*		response="200",
	*		description="Category Status Updated ",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/ 

    /* *********************************************************************
	*	TableList ADDONS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/addon/{id}",
	*	operationId="apiv1.shop.addon",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	    @OA\Parameter(
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
	*		description="Returns All Addons of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/ 

	/* *********************************************************************
	*	LIST ADDONS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/addonslist/{id}",
	*	operationId="apiv1.shop.addonslist",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	
	*	@OA\Response(
	*		response="200",
	*		description="All Addons of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/ 


	/* *********************************************************************
	* ADDONS STATUS UPDATE
 	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/addon/{id}/updateStatus",
	*	operationId="apiv1.shop.updateStatus",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Addon Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	
	*	@OA\Response(
	*		response="200",
	*		description="Status Updated ",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/ 




	/* *********************************************************************
	*	STORE ADDONS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/addons",
	*	operationId="api.v1.shop.addons",
	*	tags={"Shop"},
	*	description="Store Add On",
	*	@OA\RequestBody(
	*		description="Store Add On",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/storeaddon"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Addon changed successfully",
	*		@OA\JsonContent(ref="#/components/schemas/storeaddon")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="storeaddon", 
	* 	required={"addon_name","store_id"}, 
	*	@OA\Property(property="addon_name", type="string" ),
	*	@OA\Property(property="store_id", type="integer" ),
	*	@OA\Property(property="addon_status", type="interger", example="1" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="storeaddonInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/storeaddon"),
	*	@OA\Schema(
	*		@OA\Property(property="addon_name", type="string"),
	*		@OA\Property(property="store_id", type="integer"),
	*		@OA\Property(property="addon_status", type="interger"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	SHOW ADDONS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/addons/{id}",
	*	operationId="apiv1.shop.addons",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Addon Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*
	*	@OA\Response(
	*		response="200",
	*		description="Returns All Addons of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	UPDATE ADDONS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/addons/{id}",
	*	operationId="api.v1.shop.updateaddons",
	*	tags={"Shop"},
	*	description="Update Add On",
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="ADDONS ID",
	*		required=true,
	*		@OA\Schema(type="integer", example="1")),
	*	@OA\RequestBody(
	*		description="Update Add On",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/updateaddon"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Addon Updated successfully",
	*		@OA\JsonContent(ref="#/components/schemas/updateaddon")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="updateaddon", 
	* 	required={"addon_name"}, 
	*	@OA\Property(property="addon_name", type="string" ),
	*	@OA\Property(property="_method", type="string", example="PATCH" ),
	*	@OA\Property(property="addon_status", type="interger", example="1" )),
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="updateaddonInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/updateaddon"),
	*	@OA\Schema(
	*		@OA\Property(property="addon_name", type="string"),
	*		@OA\Property(property="_method", type="string"),
	*		@OA\Property(property="addon_status", type="interger"))
	*	}
	* )
	*/

	/* *********************************************************************
	*	Addons Delete 
	**********************************************************************/

	/**
	*@OA\Delete(
	*	path="/api/v1/shop/addons/{id}",
	*	operationId="api.v1.shop.deleteaddons",
	*	tags={"Shop"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Add ons id",
	*		required=true,
	*		@OA\Schema(type="integer", example="1")),
	*	@OA\Response(
	*		response="200",
	*		description="Addon deleted successfully",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	TableList PRODUCTS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/itemsindex/{id}",
	*	operationId="apiv1.shop.itemsindex",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	    @OA\Parameter(
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
	*		description="Returns All Items of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/ 

	/* *********************************************************************
	*	LIST PRODUCTS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/items/{id}",
	*	operationId="apiv1.shop.items",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	
	*	@OA\Response(
	*		response="200",
	*		description="All Iems of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	* PRODUCT STATUS UPDATE
 	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/items/{id}/updateStatus",
	*	operationId="apiv1.shop.updateitemsStatus",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Product Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	
	*	@OA\Response(
	*		response="200",
	*		description="Status Updated ",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/ 


	/* *********************************************************************
	*	STORE PRODUCTS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/items",
	*	operationId="api.v1.shop.storeitems",
	*	tags={"Shop"},
	*	description="Store Items",
	*	@OA\RequestBody(
	*		description="Store Items",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/storeitems"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Items Add successfully",
	*		@OA\JsonContent(ref="#/components/schemas/storeitems")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="storeitems", 
	* 	required={"item_name","store_id","store_category_id","item_price","picture"}, 
	*	@OA\Property(property="item_name", type="string" ),
	*	@OA\Property(property="store_id", type="integer" ),
	*	@OA\Property(property="item_description", type="TEXT" ),
	*	@OA\Property(property="store_category_id", type="integer" ),
	*	@OA\Property(property="is_veg",type="string", enum={"Pure Veg", "Non Veg"} ),
	*	@OA\Property(property="item_price",type="float"),
	*	@OA\Property(property="item_discount",type="float"),
	*	@OA\Property(property="item_discount_type",type="string", enum={"PERCENTAGE", "AMOUNT"}),
	*	@OA\Property(property="picture",type="file"),
	*	@OA\Property(property="addon[]", type="array", @OA\Items( type="integer"),example="1"),
	*	@OA\Property(property="addon_price[]", type="array", @OA\Items( type="float"),example="12.5" ),
	*	@OA\Property(property="status", type="interger", example="1" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="storeitemsInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/storeitems"),
	*	@OA\Schema(
	*	@OA\Property(property="item_name", type="string" ),
	*	@OA\Property(property="store_id", type="integer" ),
	*	@OA\Property(property="item_description", type="TEXT" ),
	*	@OA\Property(property="store_category_id", type="integer" ),
	*	@OA\Property(property="is_veg",type="string", enum={"Pure Veg", "Non Veg"} ),
	*	@OA\Property(property="item_price",type="float"),
	*	@OA\Property(property="item_discount",type="float"),
	*	@OA\Property(property="item_discount_type",type="string", enum={"PERCENTAGE", "AMOUNT"}),
	*	@OA\Property(property="picture",type="file"),
	*	@OA\Property(property="addon[]", type="array", @OA\Items( type="integer"),example="1"),
	*	@OA\Property(property="addon_price[]", type="array", @OA\Items( type="float"),example="12.5" ),
	*	@OA\Property(property="status", type="interger", example="1" ))
	*	}
	* )
	*/

	/* *********************************************************************
	*	UPDATE PRODUCTS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/items/{id}",
	*	operationId="api.v1.shop.updateitems",
	*	tags={"Shop"},
	*	description="Update Items",
	*	@OA\RequestBody(
	*		description="Update Items",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/updateitems"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Items Update successfully",
	*		@OA\JsonContent(ref="#/components/schemas/updateitems")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="updateitems", 
	* 	required={"item_name","store_id","store_category_id","item_price"}, 
	*	@OA\Property(property="item_name", type="string" ),
	*	@OA\Property(property="store_id", type="integer" ),
	*	@OA\Property(property="item_description", type="TEXT" ),
	*	@OA\Property(property="store_category_id", type="integer" ),
	*	@OA\Property(property="is_veg",type="string", enum={"Pure Veg", "Non Veg"} ),
	*	@OA\Property(property="item_price",type="float"),
	*	@OA\Property(property="item_discount",type="float"),
	*	@OA\Property(property="item_discount_type",type="string", enum={"PERCENTAGE", "AMOUNT"}),
	*	@OA\Property(property="picture",type="file"),
	*	@OA\Property(property="_method", type="string", example="PATCH" ), 
	*	@OA\Property(property="addon[]", type="array", @OA\Items( type="integer"),example="1"),
	*	@OA\Property(property="addon_price[]", type="array", @OA\Items( type="float"),example="12.5" ),
	*	@OA\Property(property="status", type="interger", example="1" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="updateitemsInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/storeitems"),
	*	@OA\Schema(
	*	@OA\Property(property="item_name", type="string" ),
	*	@OA\Property(property="store_id", type="integer" ),
	*	@OA\Property(property="item_description", type="TEXT" ),
	*	@OA\Property(property="store_category_id", type="integer" ),
	*	@OA\Property(property="is_veg",type="string", enum={"Pure Veg", "Non Veg"} ),
	*	@OA\Property(property="item_price",type="float"),
	*	@OA\Property(property="item_discount",type="float"),
	*	@OA\Property(property="item_discount_type",type="string", enum={"PERCENTAGE", "AMOUNT"}),
	*	@OA\Property(property="picture",type="file"),
	*	@OA\Property(property="_method", type="string", example="PATCH" ),
	*	@OA\Property(property="addon[]", type="array", @OA\Items( type="integer"),example="1"),
	*	@OA\Property(property="addon_price[]", type="array", @OA\Items( type="float"),example="12.5" ),
	*	@OA\Property(property="status", type="interger", example="1" ))
	*	}
	* )
	*/

	/* *********************************************************************
	*	Items Delete 
	**********************************************************************/

	/**
	*@OA\Delete(
	*	path="/api/v1/shop/items/{id}",
	*	operationId="api.v1.shop.deleteitems",
	*	tags={"Shop"},	
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Items id",
	*		required=true,
	*		@OA\Schema(type="integer", example="1")),
	*	@OA\Response(
	*		response="200",
	*		description="Items deleted successfully",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	LIST STORE TIMINGS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/shopstiming",
	*	operationId="apiv1.shop.shopstiming",
	*	tags={"Shop"},
	*	@OA\Response(
	*		response="200",
	*		description="All timings of Particular Store",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	    security={ {"Shop": {}} },
	* )
	*/



	/* *********************************************************************
	*	UPDATE STORE TIMINGS
	**********************************************************************/

	/**
	*@OA\Post(
	*	path="/api/v1/shop/shopstiming",
	*	operationId="api.v1.shop.updateshopstiming",
	*	tags={"Shop"},
	*	description="Update Items",
	*	@OA\RequestBody(
	*		description="Update Items",
	*		@OA\MediaType(
	*			mediaType="multipart/form-data",
	*			@OA\Schema(ref="#/components/schemas/updateshopstiming"))
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Items Update successfully",
	*		@OA\JsonContent(ref="#/components/schemas/updateshopstiming")),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*        security={ {"Shop": {}} },
	* )
	*/
	/**
	* @OA\Schema(schema="updateshopstiming", 
	*	@OA\Property(property="_method", type="string", example="PATCH" ), 
	*	@OA\Property(property="day['ALL']", type="array", @OA\Items( type="string",enum={"ALL","SUN","MON","TUE","WED","THU","FRI","SAT"}),example="day['ALL'],day['SUN'],day['MON']"),
	*	@OA\Property(property="hours_opening['ALL']", type="array", @OA\Items( type="time"),example="hours_opening['SUN']=12:00" ),
	*	@OA\Property(property="hours_closing['ALL']", type="array", @OA\Items( type="time"),example="hours_closing['SUN']=11:00" ))
	* 
	*/ 
	/**
	*@OA\Schema(
	*	schema="updateshopstimingInput",
	*	type="object",
	*	allOf={
	*	@OA\Schema(ref="#/components/schemas/storeitems"),
	*	@OA\Schema(
	*	@OA\Property(property="_method", type="string", example="PATCH" ), 
	*	@OA\Property(property="day['ALL']", type="array", @OA\Items( type="string",enum={"ALL","SUN","MON","TUE","WED","THU","FRI","SAT"}),example="day['ALL'],day['SUN'],day['MON']"),
	*	@OA\Property(property="hours_opening['ALL']", type="array", @OA\Items( type="time"),example="hours_opening['SUN']=12:00" ),
	*	@OA\Property(property="hours_closing['ALL']", type="array", @OA\Items( type="time"),example="hours_closing['SUN']=11:00" ))
	*	}
	* )
	*/





  /* *********************************************************************
	*	SHOP ORDER HISTORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/shoprequesthistory?",
	*	operationId="api.v1.shop.shoprequesthistory",
	*	tags={"Shop"},
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
	*		description="ACCEPTED / COMPLETED / CANCELLED  / history . if ACCEPTED, 'Ongoing Requests'. if history, 'COMPLETED', All Past Completed 		 *		 Orders.   if history, 'CANCELLED', All Past CANCELLED Orders ",
	*		required=false,
	*		@OA\Schema(type="string", example="COMPLETED")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns ",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/

	/* *********************************************************************
	*	SHOW ORDER DETAILS
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/requesthistory/{id}",
	*	operationId="apiv1.shop.requesthistory",
	*	tags={"Shop"},
	*	@OA\Parameter(
	*		name="id",
	*		in="path",
	*		description="Store Order Id",
	*		required=true,
	*		@OA\Schema(type="integer")
	*	),
	*	@OA\Response(
	*		response="200",
	*		description="Returns Store Order Details with Invoice",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/ 


	/* *********************************************************************
	*	SHOP ORDER HISTORY
	**********************************************************************/

	/**
	*@OA\Get(
	*	path="/api/v1/shop/dispatcher/orders?",
	*	operationId="api.v1.shop.dispatcher/orders",
	*	tags={"Shop"},	
	*	@OA\Parameter(
	*		name="type",
	*		in="query",
	*		description="ACCEPTED /ORDERED/ Dispacther . if ORDERED, 'Incoming  Requests'. if  'ACCEPTED', All Ongoing Orders",
	*       required=false,  
	*		@OA\Schema(type="string", example="ORDERED")),
	*	@OA\Response(
	*		response="200",
	*		description="Returns All Icoming and Accepted Orders",
	*		@OA\JsonContent()
	*	),
	*	@OA\Response(
	*		response="422",
	*		description="Error: Unprocessable entity. When required parameters were not supplied."),
	*	security={ {"Shop": {}} },
	* )
	*/


}