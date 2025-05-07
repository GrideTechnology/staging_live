 <!DOCTYPE html>
<html>
<head>
<title>Coming Soon</title>
<style>

body {
	margin:0;
}

#container {
	text-align: center;
	vertical-align: middle;
	height: 100%; 
	width: 100%;
	color:#fff;
	position: fixed;
	/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#1e5799+0,ed00cd+100 */
	background: #1e5799; /* Old browsers */
	background: -moz-linear-gradient(-45deg, #1e5799 0%, #ed00cd 100%); /* FF3.6-15 */
	background: -webkit-linear-gradient(-45deg, #1e5799 0%,#ed00cd 100%); /* Chrome10-25,Safari5.1-6 */
	background: linear-gradient(135deg, #1e5799 0%,#ed00cd 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#ed00cd',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
}

h1 {
	font-size: 140px;
	font-family: Helvetica, sans-serif;
	margin-top: 250px;
	width: 100%;
	float: left;
	text-align: center;
}
</style>
</head>
<body>

<div id="container">
	<h1>Coming Soon</h1>
</div>
<script src="{{ url('js/socket.io.js')}}"></script>
<script>
    // alert('yy');
    
	
    // var host = "{{config('goober.SOCKET_HOST')}}";
    var host = "http://localhost:8990";
    // var host = "http://dev6.spaceo.in/project/getagoober";

    
    console.log(host);
    // console.log( 'test' );
    var port = "{{config('goober.SOCKET_PORT')}}";
    // var port = "socket/socket.io";


    console.log(port);
    // var socket = io.connect(host,{path: port});
    var socket = io.connect("http://localhost:8990",{ query: {'user_id':31, 'is_online':1, 'isUser':1}});
    // console.log('DRIVER_LOCATION',DRIVER_LOCATION);
    socket.on('connect', function (data) {        
        console.log("Socket Connect Successfully.");
    });

    socket.on('disconnect', function() {
        console.log( 'disconnect' );
    });

    /* To call socket function: It will be called when driver location is changed*/
    // var data = {
    //     iUserId : '104',        
    // };
    // socket.emit("connect_driver",data);
    // var data = {
    //     iUserId : '105',        
    // };
    // socket.emit("connect_driver",data);

    // var data = {
    //     iUserId : '433',
    // };
    // socket.emit("connect_customer",data);

    // var data = {
    //     iUserId : '139',        
    // };
    // socket.emit("connect_driver",data);
    // var data = {
    //     iUserId : '141',        
    // };
    // socket.emit("connect_driver",data);
    // var data = {
    //     iUserId : '426',        
    // };
    // socket.emit("connect_driver",data);

    // var data = {
    //     iUserId : '438',        
    // };
    // socket.emit("connect_driver",data);
    
    
    // var data = {
    //     iRiderId : '304',        
    //     dcLatitude: '30.31706410',//'23.0201818',
    //     dcLongitude: '-86.12943650',//'72.4396596',
    //     iRequestFrom : '2' 
    // };
    // // // (iRequestFrom : 1 == Request from user, 2 == Request from driver)
    // socket.emit("get_nearby_driver",data);

//     iUserId: 139,
//   dcLatitude: '30.316585',
//   dcLongitude: '-86.127700',
//   iTripId: ''
    // var data = {
    //      :'',
    //     iUserId:426,
    //     iTripId:'',
    //     dcLatitude: '30.31709900',
    //     dcLongitude: '-86.12937200',
    //     iAngle : '45'
    // };
    // socket.emit("set_driver_location",data);

    // var data = {
    //     iRiderId: 433,
    //     iUserId: 432,
    //     iTripId: '2228',
    //     dcLatitude: '30.31706410',//'30.31706410',//'23.0965174',
    //     dcLongitude: '-86.12943650',//'-86.12943650',//'72.5991059',
    //     tiTripStatus:1,
    //     iAngle : '90'
    // };
    // socket.emit("set_driver_location",data);

    // var data = {
    //     iUserId:'105',
    //     iTripId:'',
    //     dcLatitude: '30.3170641',
    //     dcLongitude: '-86.1294365'
    // };
    // socket.emit("set_driver_location",data);
    // var data = {
    //     iTripId : '135',
    //     iDriverId : '148',
    //     iRiderId : '1',
    // };
    // socket.emit("trip_request",data);

    
    // /* To recieve the socket call*/
    // socket.on('get_live_order_location_from_driver', (data) => {
    //     console.log('Current Driver Location =========>'+data.dcLatitude+","+data.dcLongitude);
    // });
    // socket.on('alert_driver_for_trip', (data) => {
    //     console.log( data );
    //     console.log('Driver received trip =========>'+data.iTripId+","+data.iDriverId+","+data.iRiderId+","+data.vSource+","+data.vDestination);
    // });

    // var data = {
    //     iTripId : '2228',
    //     iTripDetailsId : '2257',
    //     iDriverId : '432',
    //     tiTripStatus : '2',
    //     tiIsOnline : '1',
    //     dcLatitude: '30.31709910',
    //     dcLongitude: '-86.12937210'
    // };
    // 0 - Trip Assigned, 1 - Go Now, 2 - Start Ride, 3 - Ride Completed, 4 - Cancelled
    // socket.emit("start_trip",data);

    // var data = {
    //     iTripId : '2269',
    //     iTripDetailsId : '2292',
    //     iDriverId : '426',
    //     tiTripStatus : '2',
    //     tiIsOnline : '1',
    //     dcLatitude: '30.3170641',
    //     dcLongitude: '-86.1294365'
    // };
    // 0 - Trip Assigned, 1 - Go Now, 2 - Start Ride, 3 - Ride Completed, 4 - Cancelled
    // socket.emit("start_trip",data);

    // socket.on('alert_cancel_trip_by_admin', (data) => {
    //     console.log(' ======== Response from cancel trip by admin ==========' );
    //     console.log( data );
    // });
    
    // socket.on('receive_driver_location', (data) => {
    //     console.log(' ======== Response from near by driver ==========' );
    //     console.log( data );
    // });
    // socket.on('receive_trip_driver_location', (data) => {
    //     console.log( data );
    // });
    
    // socket.on('alert_driver_for_cancel_trip', (data) => {
    //     console.log( '=======Cancel trip =====' );
    //     console.log( data );
    // });

    // socket.on('alert_rider_ongoing_trip', (data) => {
    //     console.log( data );
    // });
</script>

</body>
</html> 