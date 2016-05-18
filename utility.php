<?php
// fueleco/api utility functions.


function fixEdgeCaseModel($make = '', $model = '', $subModel = '', $debug = false)
{
    $make = strtoupper($make);
    
    if ($make == 'MAZDA') {
        $token = 'MAZDA';
        $nPosExtra = strpos($model, $token);
        if ($debug) { echo "*** nPosExtra == [".$nPosExtra."] | ".$token."<br/>"; }
        if ($nPosExtra !== false && $nPosExtra >= 0) {
            $model = substr($model, strlen($token));
            if ($debug) { echo "*** NOW Searching for [".$model."]<br/>"; }
        }
    }
    
    if ($make == 'FORD') {
        $token = "F-";
        $nPosExtra = strpos($model, $token);
        if ($debug) { echo "*** nPosExtra == [".$nPosExtra."] | ".$token."<br/>"; }
        if ($nPosExtra !== false && $nPosExtra == 0) {
            $model = 'F' . substr($model, strlen($token));
            if ($debug) { echo "*** NOW Searching for [".$model."]<br/>"; }
        }
    }
    
    if ($make == 'MERCEDES-BENZ') {
        $searchArea = $subModel;
        $nPosModel = strpos($searchArea, 'Kompressor Sport');
        if ($nPosModel !== false && $nPosModel >= 0) {
            $subModel = 'Kompressor Sport';
            if ($debug) { echo "*** UPDATED subModel == [".$subModel."]<br/>"; }
        } else {
            $nPossubModel = strpos($searchArea, 'Kompressor');
            if ($nPosModel !== false && $nPosModel >= 0) {
                $subModel = 'Kompressor';
                if ($debug) { echo "*** UPDATED subModel == [".$subModel."]<br/>"; }
            }
        }
    }
    
    if ($make == 'CADILLAC') {
        $token = "Wagon";
        $nPosExtra = strpos($model, $token);
        if ($debug) { echo "*** nPosExtra == [".$nPosExtra."] | ".$token."<br/>"; }
        if ($nPosExtra !== false && $nPosExtra >= 0) {
            $model = substr($model, 0, $nPosExtra - 1); // Remove leading space.
            if ($debug) { echo "*** NOW Searching for [".$model."]<br/>"; }
        }
    }
    
    if ($make == 'INFINITI') {
        // Strip model digits from model name.
        $model = preg_replace('/\d+/u', '', $model);
    }
    
    if ($make == 'MINI') {
        $token = "Cooper Hardtop";
        $nPosExtra = strpos($model, $token);
        if ($debug) { echo "*** nPosExtra == [".$nPosExtra."] | ".$token."<br/>"; }
        if ($nPosExtra !== false && $nPosExtra >= 0) {
            $model = 'Cooper';
            if ($debug) { echo "*** NOW Searching for [".$model."]<br/>"; }
        }
    }
    
    return array('model' => $model, 'subModel' => $subModel);
}

function getFuelEconomy($vehicle = null, $debug = false)
{
    // Init params to load search page settings.
    $result = array();
    $params = array();
    $params['host'] = 'www.fueleconomy.gov';
    $params['accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
    $params['accept-encoding'] = 'deflate';
    $params['accept-language'] = 'en-US,en;q=0.8';
    $referer = '';
    $headers = getRequestHeaders($params, $referer);
    $ecoResults = array();
    
    if ($vehicle != null && isset($vehicle['year']) 
        && isset($vehicle['make']) && isset($vehicle['model'])
        && isset($vehicle['subModel'])) {
        $year         = $vehicle['year'];
        $make         = $vehicle['make'];
        $model        = $vehicle['model'];
        $subModel     = $vehicle['subModel'];
        $result       = fixEdgeCaseModel($make, $model, $subModel, $debug);
        $model        = $result['model'];
        $subModel     = $result['subModel'];
        $urlEcoBase   = 'https://www.fueleconomy.gov/feg';
        $urlEcoCookie = $urlEcoBase.'/bymodel/'.$make.$year.'.shtml';
        
        // First Load page to get Session ID
        $params     = array();
        $url        = $urlEcoCookie;
        $userAgent  = $GLOBALS['userAgents'][0];
        $cookieFile = 'fueleconomy.gov.txt';
        $curlResult = doCurl('get', $url, $headers, $userAgent, $cookieFile, $params);
        
        // Get all the pages of search results.
        if ($curlResult['status'] == 'success') {
            
            $tokenDivStart = 'class="nocircle"';
            $tokenDivEnd = '</div>';
            $response = getInnerText($curlResult['data'], $tokenDivStart, $tokenDivEnd, $debug);
            if ($response['status'] == 'success') {
                
                // Now that we have our source snippet search this area.
                $sourceDiv = $response['data'];
                $items     = array();
                
                if (!is_null($sourceDiv) && $sourceDiv != '') {
                    /*
                    <ul class="nocircle">
                         <li><a href="../bymodel/2007_Lincoln_MKX.shtml">Lincoln  MKX</a></li>
                         <li><a href="../bymodel/2007_Lincoln_MKZ.shtml">Lincoln  MKZ</a></li>
                         <li><a href="../bymodel/2007_Lincoln_Mark_LT.shtml">Lincoln  Mark LT</a></li>
                         <li><a href="../bymodel/2007_Lincoln_Navigator.shtml">Lincoln  Navigator</a></li>
                         <li><a href="../bymodel/2007_Lincoln_Town_Car.shtml">Lincoln  Town Car</a></li>
                    </ul>
                    */
                    $tokenItemStart = '<li>';
                    $tokenItemEnd   = '</li>';
                    $response = getInnerTextMulti($sourceDiv, $tokenItemStart, $tokenItemEnd);
                    if ($response['status'] == 'success') {
                        if ($debug) { echo "Result count == ".count($response['data'])."<br/>"; }
                        foreach($response['data'] as $item) {
                            if ($debug) { echo "*** Parsing result<br/>"; }
                            $vUrl       = $urlEcoBase.getInnerText($item, 'href="..', '">')['data'];
                            $vMakeModel = trim(getInnerText($item, '">'.$make, '</a>')['data']);
                            if ($debug) {
                                echo "--- URL        == [".$vUrl."]<br/>";
                                echo "--- Make/Model == [".$vMakeModel."]<br/>";
                                echo "*** Searching for [".$model."]<br/>";
                            }
                            $nPosModel = strpos($vMakeModel, $model);
                            if ($debug) { echo "--- nPosModel        == [".$nPosModel."]<br/>"; }
                            if ($nPosModel !== false && $nPosModel >= 0) {
                                if ($debug) { echo "!!! FOUND IT<br/>"; }
                                
                                $curlResult = doCurl('get', $vUrl, $headers, $userAgent, $cookieFile, $params);
                                if ($curlResult['status'] == 'success') {
                                    // Parse the result and find the vehicle.
                                    $ecoResults = getEcoResults( $curlResult['data'], $vehicle, $debug);
                                    $result = $ecoResults['data'];
                                }
                                
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $result;
}

function getEcoResults($source = '', $vehicle, $debug = false)
{
    
    // Find the start and end tokens for the economy data.
    $result = array();
    $tokenDivStart  = 'id="main-content"';
    $tokenDivEnd    = '!-- end of #main-content -->';
    $sourceDiv = null;
    
    //$vehicle['model']
    
    // Fix engineSize.
    $model      = $vehicle['model'];
    $engineSize = $vehicle['engineSize'];
    $lenEngine  = strlen($engineSize);
    if (substr($engineSize, $lenEngine-1) == '0') {
        $engineSize = substr($engineSize, 0, $lenEngine -1);
    }
    
    $cylinders    = $vehicle['cylinders'];
    $transmission = $vehicle['transmission'];
    $model        = $vehicle['model'];
    $subModel     = fixSubModel($vehicle['subModel'], $engineSize);
    $drivetrain   = getDrivetrain($vehicle['drivetrain']);
    $speed        = $vehicle['speed'];
    $fuel         = $vehicle['fuel'];
    
    $response = getInnerText($source, $tokenDivStart, $tokenDivEnd, $debug);
    if ($response['status'] == 'success') {
        
        // Now that we have our source snippet search this area.
        $sourceDiv = $response['data'];
        $items     = array();
        
        if (!is_null($sourceDiv) && $sourceDiv != '') {
            $tokenItemStart = 'class="rowGroup vehicle"';
            $tokenItemEnd   = 'class="rowGroup vehicle"';
            $response = getInnerTextMulti($sourceDiv, $tokenItemStart, $tokenItemEnd);
            if ($debug) {
                echo "FOUND snippet<br/>";
            }
            if ($response['status'] == 'success') {
                // Get the car make.
                //<td colspan="7"><a href="Find.do?action=sbs&amp;id=36016">2015 BMW i3 BEV</a> Automatic (A1), Electricity</td>
                // Get the <td> text.
                if ($debug) { echo "Result count == ".count($response['data'])."<br/>"; }
                // Listing ids not found.
                $vMake       = $vehicle['make'];
                $vModel      = $vehicle['model'];
                $vSubModel   = $vehicle['subModel'];
                $vDrivetrain = $vehicle['drivetrain'];
                
                $token = "MAZDA";
                $nPosExtra = strpos($vModel, $token);
                if ($debug) { echo "*** nPosExtra == [".$nPosExtra."] | ".$token."<br/>"; }
                if ($nPosExtra !== false && $nPosExtra >= 0) {
                    $vModel = 'Mazda '.substr($vModel, strlen($token));
                    if ($debug) { echo "*** NOW Searching for [".$vModel."]<br/>"; }
                }
                
                $token = "F-";
                $nPosExtra = strpos($vModel, $token);
                if ($debug) { echo "*** nPosExtra == [".$nPosExtra."] | ".$token."<br/>"; }
                if ($nPosExtra !== false && $nPosExtra == 0) {
                    $vModel = 'F' . substr($vModel, strlen($token));
                    if ($debug) { echo "*** NOW Searching for [".$vModel."]<br/>"; }
                }
                
                $vSubModel = fixSubModel($vSubModel, $engineSize);
                
                // Search SubModel + Drivetrain.
                $items = getMpg( $response['data'], $vMake, $vModel, $vSubModel, $cylinders, $engineSize, $transmission, $drivetrain, $speed, $fuel, $debug);
                if ($items == array()) {
                    // Search Model + Drivetrain.
                    $items = getMpg( $response['data'], $vMake, $vModel, '', $cylinders, $engineSize, $transmission, $drivetrain, $speed, $fuel, $debug);
                    if ($items == array()) {
                        // Search SubModel w/o Drivetrain.
                        $items = getMpg( $response['data'], $vMake, $vModel, $vSubModel, $cylinders, $engineSize, $transmission, '', $speed, $fuel, $debug);
                        if ($items == array()) {
                                // Search Model w/o Drivetrain.
                                $items = getMpg( $response['data'], $vMake, $vModel, '', $cylinders, $engineSize, $transmission, '', $speed, $fuel, $debug);
                                if ($items == array()) {
                                    // Set default values.
                                    $items['gas']['mpg']['city'] = '';
                                    $items['gas']['mpg']['combined'] = '';
                                    $items['gas']['mpg']['highway'] = '';
                            }
                        }
                    }
                }
            } else {
                // Listing ids not found.
                $items['gas']['mpg']['city'] = '';
                $items['gas']['mpg']['combined'] = '';
                $items['gas']['mpg']['highway'] = '';
            }
            
            if ( count($items) == 1) {
                $result = array('status'=>'success', 'data'=>$items, 'msg'=>'Vehicle found.');
            } else {
                $result = array('status'=>'success', 'data'=>$items, 'msg'=>'No vehicle found.');
            }

        } else {
            // No error div.
            $result = array('status'=>'error', 'data'=>$items, 'msg'=>'Error parsing vehicles.');
        }
    }
    
    return $result;
}

/*
    Notes:
        $vMake = $vehicle['make'];
        $vSubModel = $vehicle['subModel'];
*/
function getMpg( $vehicles, $vMake, $vModel, $vSubModel, $cylinders, $engineSize, $transmission, $drivetrain, $speed, $fuel, $debug)
{
    $MPG_TO_LKM = 235.215;
    $items = array();
    foreach($vehicles as $item) {
        if ($debug) { echo ">>> Parsing result<br/>"; }
        // Search for model if there is no subModel.
        $description = trim(getInnerText($item, 'a href=', '/a>')['data']);
        if ($debug) { echo ">>> DESCRIPTION == [".$description."]<br/>"; }
        $modelCheck = false;
        if ($vSubModel == '') {
            // Find the model.
            if ($debug) { echo "--- FIND the model [".$vModel."]<br/>"; }
            $nPosModel = strpos($description, $vModel);
            if ($nPosModel !== false && $nPosModel >= 0) {
                if ($debug) { echo "!!! FOUND model [".$vModel."]<br/>"; }
                if ($drivetrain != '') {
                    $nPosDrivetrain = strpos($description, $drivetrain);
                    if ($debug) { echo "--- FIND the drivetrain [".$drivetrain."]<br/>"; }
                    if ($nPosDrivetrain !== false && $nPosDrivetrain >= 0) {
                        if ($debug) { echo "!!! FOUND drivetrain [".$drivetrain."] at position == [".$nPosDrivetrain."]<br/>"; }
                        $modelCheck = true;
                    }
                } else {
                    $modelCheck = true;
                }
            }
        } else {
            if ($debug) { echo "--- FIND the subModel [".$vSubModel."]<br/>"; }
            $nPosSubModel = strpos($description, $vSubModel);
            if ($nPosSubModel !== false && $nPosSubModel >= 0) {
                if ($debug) { echo "!!! FOUND subModel [".$vSubModel."]<br/>"; }
                if ($drivetrain != '') {
                    $nPosDrivetrain = strpos($description, $drivetrain);
                    if ($debug) { echo "--- FIND the drivetrain [".$drivetrain."]<br/>"; }
                    if ($nPosDrivetrain !== false && $nPosDrivetrain >= 0) {
                        if ($debug) { echo "!!! FOUND drivetrain [".$drivetrain."] at position == [".$nPosDrivetrain."]<br/>"; }
                        $modelCheck = true;
                    }
                } else {
                    $modelCheck = true;
                }
            }
        }
        
        if ($modelCheck == true) {
            $options = trim(getInnerText($item, '/a>', '/th>')['data']);
            $tokenSearch = $cylinders.' cyl, '. $engineSize.' L, '.$transmission;
            if ($debug) {
                echo ">>> OPTIONS     == [".$options."]<br/>";
                echo "--- FIND the options [".$tokenSearch."]<br/>";
            }
            $nPosCylinders    = strpos($options, $cylinders.' cyl');
            $nPosEngineSize   = strpos($options, $engineSize.' L');
            $nPosTransmission = strpos($options, $transmission);
            
            if ($debug) {
                echo "--- nPosCylinders == [".$nPosCylinders."]<br/>";
                echo "--- nPosEngineSize == [".$nPosEngineSize."]<br/>";
                echo "--- nPosTransmission == [".$nPosTransmission."]<br/>";
            }
            
            $fuelE86 = false;
            
            if (($nPosCylinders    !== false && $nPosCylinders    >= 0) &&
                ($nPosEngineSize   !== false && $nPosEngineSize   >= 0) &&
                ($nPosTransmission !== false && $nPosTransmission >= 0)) {
                    
                if ($debug) { echo "!!! FOUND options [".$tokenSearch."]<br/>"; }
                
                // Do check for extended options.
                // "extended":{"drivetrain":{"4x4 Shift on the Fly ":"checked","6-SPEED A\/T":"checked","FLEX FUEL CAPABILITY":"checked"
                $extendedMatch = true;
                
                // Get speed options.
                $nPosHasSpeed = strpos($options, '-spd');
                if ($nPosHasSpeed !== false && $nPosHasSpeed >= 0) {
                    if ($debug) { echo "--- Do a SPEED match<br/>"; }
                    if ($speed != '') {
                        // Match on speed.
                        if ($debug) { echo "--- FIND the speed [".$speed.'-spd'."]<br/>"; }
                        $nPosExtended = strpos($options, $speed.'-spd');
                        if ($nPosExtended !== false && $nPosExtended >= 0) {
                            if ($debug) { echo "!!! FOUND speed [".$speed.'-spd'."] at position == [".$nPosExtended."]<br/>"; }
                            $extendedMatch = true;
                        } else {
                            $extendedMatch = false;
                        }
                    }
                } else {
                    if ($debug) { echo "--- No SPEED match needed<br/>"; }
                }
                
                // Does vehicle support E85 Flex Fuel?
                $aFuels = array('', 'GASOLINE', 'GAS');
                if (!in_array(strtoupper($fuel), $aFuels)) {
                    if ($debug) { echo "--- FIND the fuel type [".$fuel."]<br/>"; }
                    $nPosExtended = strpos($description, $fuel);
                    if ($nPosExtended !== false && $nPosExtended >= 0) {
                        if ($debug) { echo "!!! FOUND fuel type [".$fuel."] at position == [".$nPosExtended."]<br/>"; }
                        $extendedMatch = true;
                    } else {
                        $extendedMatch = false;
                    }
                }
                
                if ($extendedMatch == true) {
                    if ($debug) {echo "*** FOUND EXTENDED MATCH ***<br/>";}
                    // Get fuel economy.
                    
                    // Get the different fuel types and return them all.
                    /*
                    <div class="mpgSummary fuel1">
                        <div class="" style="float: left; margin: 5px 0 0 26px">
                            <div style="font-size: 25px; font-weight: 600"><span class="context">Combined MPG:</span>16</div>
                            <div class="rating-type" style="margin-top: -4px">combined</div>
                            <div class="rating-type">city/highway</div>
                        </div>
                        <div class="" style="float: left; margin: 10px 0 0 12px">
                            <div class="unitsLabel">MPG</div>
                            <div style="margin-top: 0">
                                <div style="width: 22px; float: left; margin-top: 0">
                                    <div class="ctyhwy"><span class="context">City MPG:</span>14</div>
                                    <div class="rating-type" style="margin: 2px 0 0 0">city</div>
                                </div>
                                <div style="float: left; margin: 0 0 0 4px">
                                        <div class="ctyhwy"><span class="context">Highway MPG:</span>20</div>
                                        <div class="rating-type" style="margin: 2px 0 0 0">highway</div>
                                </div>
                                <div style="clear: both"></div>
                            </div>
                        </div>
                        <div style="clear: both"></div>
                        <div class="centerText" style="margin-top: 5px">
                            <span style="font-size: 10px; margin-top: 12px;">6.3 gals/100 miles </span>
                        </div>
                    </div>
                    */
                    /*
                    <div class="mpgSummary E85">
                        <div class="" style="float: left; margin: 5px 0 0 26px">
                            <div style="font-size: 25px; font-weight: 600"><span class="context">Combined MPG:</span>12</div>
                            <div class="rating-type" style="margin-top: -4px">combined</div>
                            <div class="rating-type">city/highway</div>
                        </div>
                        <div class="" style="float: left; margin: 10px 0 0 12px">
                            <div class="unitsLabel">MPG</div>
                            <div style="margin-top: 0">
                                <div style="width: 22px; float: left; margin-top: 0">
                                    <div class="ctyhwy"><span class="context">City MPG:</span>10</div>
                                    <div class="rating-type" style="margin: 2px 0 0 0">city</div>
                                </div>
                                <div style="float: left; margin: 0 0 0 4px">
                                        <div class="ctyhwy"><span class="context">Highway MPG:</span>14</div>
                                        <div class="rating-type" style="margin: 2px 0 0 0">highway</div>
                                </div>
                            <div style="clear: both"></div>
                            </div>
                        </div>
                        <div style="clear: both"></div>
                        <div class="centerText" style="margin-top: 5px">
                            <span style="font-size: 10px; margin-top: 12px;">8.3  gal/100mi</span>
                        </div>
                    </div>
                    */
                    // Get gasoline fuel results.
                    $fuelType = getInnerText($item, 'class="mpgSummary fuel1"', 'style="clear: both"', $debug)['data'];
                    if ($fuelType != '') {
                        $city     = getInnerText($fuelType, 'City MPG:</span>', '/div>')['data'];
                        $city     = preg_replace("/[^0-9,.]/", "", $city);
                        $combined = getInnerText($fuelType, 'Combined MPG:</span>', '/div>')['data'];
                        $combined = preg_replace("/[^0-9,.]/", "", $combined);
                        $highway  = getInnerText($fuelType, 'Highway MPG:</span>', '/div>')['data'];
                        $highway  = preg_replace("/[^0-9,.]/", "", $highway);
                        
                        $items['gas']['mpg']['city']     = $city;
                        $items['gas']['mpg']['combined'] = $combined;
                        $items['gas']['mpg']['highway']  = $highway;
                        $items['gas']['lkm']['city']     = number_format($MPG_TO_LKM / $city, 2);
                        $items['gas']['lkm']['combined'] = number_format($MPG_TO_LKM / $combined, 2);
                        $items['gas']['lkm']['highway']  = number_format($MPG_TO_LKM / $highway, 2);
                    }
                    $fuelType = getInnerText($item, 'class="mpgSummary E85"', 'style="clear: both"', $debug)['data'];
                    if ($fuelType != '') {
                        $city     = getInnerText($fuelType, 'City MPG:</span>', '/div>')['data'];
                        $city     = preg_replace("/[^0-9,.]/", "", $city);
                        $combined = getInnerText($fuelType, 'Combined MPG:</span>', '/div>')['data'];
                        $combined = preg_replace("/[^0-9,.]/", "", $combined);
                        $highway  = getInnerText($fuelType, 'Highway MPG:</span>', '/div>')['data'];
                        $highway  = preg_replace("/[^0-9,.]/", "", $highway);
                        
                        $items['e85']['mpg']['city']     = $city;
                        $items['e85']['mpg']['combined'] = $combined;
                        $items['e85']['mpg']['highway']  = $highway;
                        $items['e85']['lkm']['city']     = number_format($MPG_TO_LKM / $city, 2);
                        $items['e85']['lkm']['combined'] = number_format($MPG_TO_LKM / $combined, 2);
                        $items['e85']['lkm']['highway']  = number_format($MPG_TO_LKM / $highway, 2);
                    }
                    break;
                }
            }
        }
    }
    
    return $items;
}

function getDrivetrain($vehicleDrivetrain = 'Front Wheel Drive')
{
    // ----------------------------------------------------------------
    // *** DriveTrain = Front Wheel Drive / Rear Wheel Drive / Four Wheel Drive     / All-Wheel Drive
    //     Drive Type = Front-Wheel       / Rear-Wheel       / Four-Wheel/All-Wheel / Four-Wheel/All-Wheel
    // &DriveTypeSel=
    // ----------------------------------------------------------------
    // ...&cbdtfwd=FWD&DriveTypeSel=FWD
    // ...&cbdtrwd=RWD&DriveTypeSel=RWD
    // ...&cbdt4wd=4WD&DriveTypeSel=4WD
    $drivetrain = '';
    
    //Four Wheel Drive":"","Front Disc\/Rear Drum Brakes":"","Front Wheel Drive":"","Locking Front Differential":"","Locking Rear Differential":"","Power Steering":"checked","Quattro AWD":"","Rear Wheel Drive":"checked",
    switch (strtoupper($vehicleDrivetrain)) {
        case 'FOUR WHEEL DRIVE' :
        case '4 WHEEL DRIVE' :
        case '4WD' :
            $drivetrain = '4WD';
            break;
        case 'ALL WHEEL DRIVE' :
        case 'AWD' :
            $drivetrain = 'AWD';
            break;
        case 'REAR WHEEL DRIVE' :
        case 'RWD' :
            $drivetrain = 'RWD';
            break;
        default :
            $drivetrain = 'FWD';
    }
    
    return $drivetrain;
}

function fixSubModel($subModel = '', $engineSize = '')
{
    // Remove engineSize from subModel.
    if ($subModel !== '' && $engineSize !== '') {
        $token = ' '.$engineSize.'L';
        $posEngineSize = strpos($subModel, $token);
        if ($posEngineSize !== false && $posEngineSize >= 0) {
            // Remove it.
            $subModel = trim(substr($subModel, 0, $posEngineSize) . substr($subModel, $posEngineSize + strlen($token)));
        } else {
            $token = ' '.$engineSize.' L';
            $posEngineSize = strpos($subModel, $token);
            if ($posEngineSize !== false && $posEngineSize >= 0) {
                // Remove it.
                $subModel = trim(substr($subModel, 0, $posEngineSize) . substr($subModel, $posEngineSize + strlen($token)));
            } else {
                $token = ' '.$engineSize.'-L';
                $posEngineSize = strpos($subModel, $token);
                if ($posEngineSize !== false && $posEngineSize >= 0) {
                    // Remove it.
                    $subModel = trim(substr($subModel, 0, $posEngineSize) . substr($subModel, $posEngineSize + strlen($token)));
                } else {
                }
            }
        }
    }
    $lenEngine = strlen($engineSize);
    if (substr($engineSize, $lenEngine-1) == '0') {
        $engineSize = substr($engineSize, 0, $lenEngine -1);
    }
    
    return $subModel;
}
