<?php
// =======================================================================================
//  fuel economy api
// =======================================================================================

// Parse the REQUST params.
$apiParams = $_REQUEST;

require_once 'core.php';
require_once 'utility.php';

$DEBUG = false;
if (isset($_REQUEST['debug'])) {
    $DEBUG = strtolower($_REQUEST['debug']);
    if ($DEBUG == 'true' || $DEBUG == 'on') {
        $DEBUG = true;
    } else {
        $DEBUG = false;
    }
}

$ACTION = '';
if (isset($_REQUEST['action'])) { $ACTION = $_REQUEST['action']; }

$logFile    = 'api_fueleco.log';
$logOutput = '';

$result = doAction($GLOBALS['ACTION']);
echo json_encode($result);

die();

function doAction($action) {
    $result     = array();
    
    switch ($action) {
        // ---------------------------------------------------------------------------------------
        case 'get_fueleco' :
        // ---------------------------------------------------------------------------------------
            $vehicleInfo = array(
                'year'=>$year,
                'make'=>$make,
                'model'=>$model,
                'subModel'=>$subModel,
                'cylinders'=>$cylinders,
                'engineSize'=>$engineSize,
                'transmission'=>$transmission,
                'drivetrain'=>$drivetrain,
                'speed'=>$speed,
                'fuel'=>$fuel
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
            
            $result = getFuelEconomy($vehicleInfo, $GLOBALS['DEBUG']);
            
            // Log activity.
            $GLOBALS['logOutput']  = '"'.gmdate('Y-m-d h:m:s', time()).'";"'.$action.'"';
            file_put_contents($GLOBALS['logFile'], $GLOBALS['logOutput']."\n", FILE_APPEND);
            break;
            
        // ---------------------------------------------------------------------------------------
        case 'default' :
        // ---------------------------------------------------------------------------------------
            $result = array('status'=>'error', 'data'=>$action, 'msg'=>'Unknown action ['.$action.'].');
            
            // Log activity.
            $GLOBALS['logOutput']  = '"'.gmdate('Y-m-d h:m:s', time()).'";"'.$action.'"';
            file_put_contents($GLOBALS['logFile'], $GLOBALS['logOutput']."\n", FILE_APPEND);
    }
    
    return $result;
}