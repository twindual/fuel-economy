# fuel-economy

API to get vehicle fuel economy from the U.S. E.P.A database off the official U.S. Government website at <a href="http://www.fueleconomy.gov/feg/ws/index.shtml" target="_blank" />fueleconomy.gov</a>.

## Usage

This PHP REST API provides has a single method and accepts parameters passed to the url:
```javascript
http://localhost/fueleco/?action=get_fueleco&year=2012&make=Honda&model=Fit
```

Results are returned as a JSON object for the first vehicle found that matches the given parameters.

```javascript
{
   "gas":{
      "mpg":{
         "city":"27",
         "combined":"29",
         "highway":"33"
      },
      "lkm":{
         "city":"8.71",
         "combined":"8.11",
         "highway":"7.13"
      }
   }
}
```
## Errors

If you are missing a required parameter it returns an error status and message as a JSON object.

```javascript
{"error":true,"message":"Missing 1 or more requried parameters: (year, make, model)"}
```

If no results are found for your given parameters it returns an error status and message as a JSON object.

```javascript
{"error":true,"message":"No data found for given parameters: (year: 2012, make: SomeMake, model: SomeModel)"}
```

## Parameters

The only required parameters are year, make, and model.

You can turn on debug output for fun by setting 'debug=on'.

All the vehicle parameters get put into a vehicle array with an element for each valid parameter.

```javascript
    $vehicleInfo = array(
        'year'=>'',
        'make'=>'',
        'model'=>'',
        'subModel'=>'',
        'cylinders'=>'',
        'engineSize'=>'',
        'transmission'=>'',
        'drivetrain'=>'',
        'speed'=>'',
        'fuel'=>''
    );
            
    if (isset($_REQUEST['year']))         { $vehicleInfo['year']         = $_REQUEST['year']; }
    if (isset($_REQUEST['make']))         { $vehicleInfo['make']         = $_REQUEST['make']; }
    if (isset($_REQUEST['model']))        { $vehicleInfo['model']        = $_REQUEST['model']; }
    if (isset($_REQUEST['subModel']))     { $vehicleInfo['subModel']     = $_REQUEST['subModel']; }
    if (isset($_REQUEST['cylinders']))    { $vehicleInfo['cylinders']    = $_REQUEST['cylinders']; }
    if (isset($_REQUEST['engineSize']))   { $vehicleInfo['engineSize']   = $_REQUEST['engineSize']; }
    if (isset($_REQUEST['transmission'])) { $vehicleInfo['transmission'] = $_REQUEST['transmission']; }
    if (isset($_REQUEST['drivetrain']))   { $vehicleInfo['drivetrain']   = $_REQUEST['drivetrain']; }
    if (isset($_REQUEST['speed']))        { $vehicleInfo['speed']        = $_REQUEST['speed']; }
    if (isset($_REQUEST['fuel']))         { $vehicleInfo['fuel']         = $_REQUEST['fuel']; }    
```

## Installation

Copy the files into your webserver directory.

## Notes

The mileage is supplied from fueleconomy.gov as mpg (miles per gallon). We then convert it to lkm (liters per 100 kilometers).
Then we return both sets of values for city, combined, and highway driving.

We scrape the site for the data instead of using the web API since test results have shown that the web API provides different 
and less data than navigating the site as a user.

Here is information on the <a href="http://www.fueleconomy.gov/feg/ws/index.shtml">API provided by fueleconomy.gov</a> if your interested. Please note that they don't offer <a href="https://en.wikipedia.org/wiki/JSON">JSON</a> as an option instead its only <a href="https://en.wikipedia.org/wiki/Comma-separated_values">CSV</a> or <a href="https://en.wikipedia.org/wiki/XML">XML</a>.

## Copyright

Copyright (C) 2016 by André Fortin <andre.v.fortin@gmail.com>

The MIT License (MIT)

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
License file for more details.
