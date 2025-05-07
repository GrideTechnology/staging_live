<!-- <div class="email_template" style="width: 600px; margin: 0px auto;font-family: 'Poppins', sans-serif !important;
 background: rgba(255, 233, 222, 0.31);padding: 20px;box-shadow: 0px 0px 5px rgba(51,51,51,0.3);">
<table align="center" class="head" width="100%">
	<tr>
		<th><img src="q-logo1.png" width="100px"></th>
	</tr>
	
</table>
<table align="center" class="temp_b" width="100%" style="text-align: center;">
	<tr>
		<td>
		<p > Restaurent Signup</p>
		</td>
	</tr>
</table>
<table width="100%" align="center" class="form_field1">
	<tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">User Name</b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">{{$request->first_name}} {{$request->last_name}}</td>
	</tr>
	<tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">E-mail</b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">{{$request->email}}</td>
	</tr>
	<tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">Mobile</b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">{{$request->mobile}}</td>
	</tr>
	<tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">Location</b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">{{$request->location}}</td>
	</tr>
	<tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">Cuisine</b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">{{$request->cuisine}}</td>
	</tr>
	<tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">Restaurent Name</b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">res_name</td>
	</tr>
	<tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">Adjuntar</b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">file</td>
	</tr> -->
	<!-- <tr>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);"><b style="color: #000;font-weight: normal;
       text-shadow: 0px 0px;">Mensaje </b></td>
		<td width="50%" style="padding: 10px;background: rgb(236, 217, 207);">{{$request->message}}</td>
	</tr> -->
<!-- </table> -->




<!-- <table align="center" width="100%" style="text-align: center;padding-top: 10px;">
	<tr>
		<td>
		<div class="copy_right">
		Copyrights
		</div>
		</td>
	</tr>
</table> -->
<!-- </div> --> 

<html lang="en">
<head>
  <title>Restaurent Signup</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

  <style type="text/css">
    .table {
      width: auto !important;
    }
    th {
      color: #7c7a79;
    }
    .heading {
      text-align: center;
      background: #7c7a79;
      color: #fff !important;
    }
    .table-bordered td, .table-bordered th {
    border: 1px solid #929394 !important;
  }

  </style>
</head>
<body>
<div class="container">
<table class="table table-bordered">
  <tbody>
    <tr>
      <th colspan="2" class="heading">
        Personal Information
      </th>
    </tr>
    <tr>
      <th width="50%">First Name</th>
      <td>{{$request->first_name}}</td>
    </tr>
   <tr>
      <th>Last Name</th>
      <td>{{$request->last_name}}</td>
    </tr>
    <tr>
      <th>Email</th>
      <td>{{$request->email}}</td>
    </tr>
    <tr>
      <th>Contact</th>
      <td>{{$request->mobile}}</td>
    </tr>
    <tr>
      <th colspan="2" class="heading">
          Restaurant Information
      </th>
    </tr>
    <tr>
      <th>Locations</th>
      <td>{{$request->location}}</td>
    </tr>
     <tr>
      <th>Cusine</th>
      <td>{{$request->cuisine}}</td>
    </tr>
     <tr>
      <th>Restaurant Name</th>
      <td>{{$request->res_name}}</td>
    </tr>
     <tr>
      <th>Restaurant Address</th>
      <td>{{$request->res_address}}</td>
    </tr>
     <tr>
      <th>Suite</th>
      <td>{{$request->suite}}</td>
    </tr>
     <tr>
      <th>Restaurant Phone</th>
      <td>{{$request->res_mobile}}</td>
    </tr>
    <tr>
      <th colspan="2" class="heading">
          Pricing
      </th>
    </tr>
    <tr>
      <th>Orders</th>
      <td>{{$request->g_deliver}} {{$request->my_deliver}}</td>
    </tr>
    <tr>
      <th colspan="2" class="heading">
          Tax Information
      </th>
    </tr>
    <tr>
      <th>Company Name</th>
      <td>{{$request->company_name}}</td>
    </tr>
    <tr>
      <th>FEIN</th>
      <td>{{$request->fein}}</td>
    </tr>
    <tr>
      <th colspan="2" class="heading">
          Payment Information
      </th>
    </tr>
    <tr>
      <th>First Name</th>
      <td>{{$request->b_fname}}</td>
    </tr>
    <tr>
      <th>Last Name</th>
      <td>{{$request->b_lname}}</td>
    </tr>
    <tr>
      <th>Bank Name</th>
      <td>{{$request->bank_name}}</td>
    </tr>
    <tr>
      <th>Routing Number</th>
      <td>{{$request->routing_number}}</td>
    </tr>
    <tr>
      <th>ACH Number</th>
      <td>{{$request->ach_number}}</td>
    </tr>
    <tr>
      <th>Email</th>
      <td>{{$request->b_email}}</td>
    </tr>
    <tr>
      <th>Phone</th>
      <td>{{$request->b_mobile}}</td>
    </tr>
  </tbody>
</table>
</div>
</body>
</html>