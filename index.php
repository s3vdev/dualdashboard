<?php
error_reporting(E_ALL);

/*
 * The MIT License
 *
 * Copyright 2018 Sven Mielke <web@ddl.bz>
 *
 * Repostitory:
 *
 * Dual Dashboard v.1.0.9 for ethOS and ethermine.org
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


# ETH Wallet
$wallet = $_GET['wallet'];

# ethosdistro subdomain to dashboard
$urlID = $_GET['sub'];

# Load ethermine JSON
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "https://ethermine.org/api/miner_new/$wallet");
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$ethermineresult = curl_exec($ch);
curl_close($ch);
$ethermine = json_decode($ethermineresult, false, 512, JSON_BIGINT_AS_STRING);

$error = '';
if (!$ethermine) {
    $error = '<h6 class="header col s12 red-text">Could not load JSON url form ehtermine.org cos to fast reloads, page will reload in 60 sec. :)</h6>';
}

#echo '<pre>';
#print_r($ethermine);
#echo '</pre>';


# Load ethosdistro JSON
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_URL, "http://$urlID.ethosdistro.com/?json=yes");
$ethosresult = curl_exec($ch2);
curl_close($ch2);
$ethosdistro = json_decode($ethosresult);

#echo '<pre>';
#print_r($ethosdistro);
#echo '</pre>';

# Load coinmarketcap api JSON
$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_URL, "https://api.coinmarketcap.com/v1/ticker/ethereum/"); #/?convert=EUR
$coinmktcapresult = curl_exec($ch3);
curl_close($ch3);
$coinmktcap = json_decode($coinmktcapresult);

#echo '<pre>';
#print_r($coinmktcap);
#echo '</pre>';

/*
// DOM
$curl = curl_init("https://ethermine.org/miners/$wallet/payouts");
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);  //set the timeout
curl_setopt($curl, CURLOPT_USERAGENT, 'Dual Dashboard/1.0 (https://ethos.phpecho.de)');  //set our 'user agent'
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FILETIME, true);
$content = curl_exec($curl);

// Quellcode als DomDocument
libxml_use_internal_errors(true);

$dom = new DOMDocument();
#$dom->validateOnParse = true;
$dom->loadHTML($content);
$xpath = new DOMXpath($dom);

$header = $xpath->query('//*[starts-with(@class, "table table-condensed table-bordered")]/tbody/tr[6]/td[3]/text()');
#$header = $xpath->query('//html/body/div[1]/div/div[2]/div/div[6]/div[2]/table/tbody/tr[6]/td[3]');
if ($header->length > 0) {
    $header = trim($header[0]->nodeValue);
} else {
    echo 'Not found';
}

die($header);
*/


# Get 100% shares
$total = $ethermine->minerStats->validShares + $ethermine->minerStats->staleShares + $ethermine->minerStats->invalidShares;

# Calculate shares in %
$shares_percent = round($ethermine->minerStats->validShares / $total * 100, 0);

# Calculate stale shares in %
$stale_shares_percent = round($ethermine->minerStats->staleShares / $total * 100, 0);

# Calculate invalid shares in %
$invalid_shares_percent = round($ethermine->minerStats->invalidShares / $total * 100, 0);

# https://converter.murkin.me/
function weiToEther($bignumber)
{
    $calc = number_format($bignumber / 1000000000000000000, 5, '.', '');
    return $calc;
}

# Convert json bigint as string (E-5)
function bigint($bignumber)
{
    $calc = number_format($bignumber, 20);
    return $calc;
}

$unpaid    = weiToEther($ethermine->unpaid);
$minPayout = weiToEther($ethermine->settings->minPayout);


# Unpaid in percent - calculate from wei
$weiToEthpercent = number_format($unpaid / $minPayout * 100, 2, '.', '');

$rawEth = weiToEther($ethermine->settings->minPayout);


# Clear averageHashrate
$clearavgHash    = str_replace(".", "", $ethermine->minerStats->averageHashrate);


$_float = explode(".", $ethermine->minerStats->averageHashrate);
$lenght = strlen($_float[1]);

//echo $ethermine->minerStats->averageHashrate .'<br>';

if ($lenght === 7)
{
    $ceil = 10000000000000; //14 - 1 MH/s
}
elseif ($lenght === 6)
{
    $ceil = 100000000000; //13 - 10 MH/s
}
elseif ($lenght === 5)
{
    $ceil = 100000000000; //12 - 100 MH/s
}
elseif ($lenght === 4)
{
    $ceil = 10000000000; //11 - 1000 MH/s
}

$createavgHash   = $clearavgHash / $ceil;
$averageHashrate = number_format($createavgHash, 1, '.', '') . ' MH/s';


# current usd balance
setlocale(LC_MONETARY, 'en_US');
$usd_balance = money_format('%i', $unpaid * $coinmktcap[0]->price_usd);

# Next payout
$hours = ($minPayout - $unpaid) / (bigint($ethermine->ethPerMin) * 60);
$next_payout = date("Y-m-d H:i:s", strtotime(sprintf("+%d hours", $hours)));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
    <title>Dual Dashboard</title>

    <!-- CSS  -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
    <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>

</head>
<div class="container">

<h4 class="header center orange-text">Dual Dashboard BETA</h4>


<div class="row center">
    <h5 class="header col s12 light">CryptoCurrency Price Ticker</h5>
</div>

<div class="row">
    <div class="col s12 m5 l4">
        <script type="text/javascript" src="https://files.coinmarketcap.com/static/widget/currency.js"></script><div class="coinmarketcap-currency-widget" data-currency="bitcoin" data-base="USD" data-secondary="BTC" data-ticker="true" data-rank="true" data-marketcap="true" data-volume="true" data-stats="USD" data-statsticker="true"></div>
        <br>
    </div>

    <div class="col s12 m5 l4">
        <script type="text/javascript" src="https://files.coinmarketcap.com/static/widget/currency.js"></script><div class="coinmarketcap-currency-widget" data-currency="ethereum" data-base="USD" data-secondary="BTC" data-ticker="true" data-rank="true" data-marketcap="true" data-volume="true" data-stats="USD" data-statsticker="true"></div>
        <br>
    </div>

    <div class="col s12 m5 l4">
        <script type="text/javascript" src="https://files.coinmarketcap.com/static/widget/currency.js"></script><div class="coinmarketcap-currency-widget" data-currencyid="2575" data-base="USD"  data-secondary="BTC"></div>
        <br>
    </div>
</div>


<div class="row center">
    <h5 class="header col s12 light">ethermine.org Dashboard</h5>
    <?= $error ?>
</div>


<?php if(isset($wallet) AND !empty($wallet)) { ?>


<div class="row">

    <div class="grid-example col s12 m6 l3">

        <div class="card blue-grey darken-1">

            <div class="card-action white-text center" style="background-color: #f0ad4e">
                <h6>Hashrates</h6>
            </div>

            <div class="card-content white-text center" style="height: 80px!important; ">
                <h6>

                    <?php
                    if ($ethermine->reportedHashRate > 0)
                    {
                        echo '<span class="tooltipped" data-position="bottom" data-delay="50" data-tooltip="This is the hashrate as reported by your miner to the pool."> '. $ethermine->reportedHashRate .'</span> -';
                    }
                    ?>

                    <span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                          data-tooltip="Your effective current hashrate.<br> It is calculated according your submitted shares using a 60 minute window.<br> It will take up to 2 hours till the displayed hashrate is accurate.<br> Deviations from your local hashrate are normal."><?= $ethermine->hashRate ?></span>
                    -
                    <span class="tooltipped" data-position="bottom" data-delay="50"
                          data-tooltip="Your average effective hashrate over the last 24 hours."><?= $averageHashrate ?></span>
                </h6>
            </div>

        </div>
    </div>

    <div class="grid-example col s12 m6 l3">
        <div class="card blue-grey darken-1">

            <div class="card-action white-text center" style="background-color: #5bc0de">
                <h6>Unpaid Balance</h6>
            </div>

            <div class="card-content white-text center" style="height: 80px!important; ">

                <h6>
                    <?= $unpaid ?>
                    <span style="font-size: smaller">
                        ($<?= $usd_balance ?>)
                        <br>Next Payout: <span class="rendered_uptime" datetime="<?= $next_payout ?>"></span>
                    </span>
                </h6>

                <div class="progress tooltipped white" data-html="true" data-position="bottom" data-delay="50"
                     data-tooltip="<?= $weiToEthpercent ?>% of your <?= $rawEth ?> ETH payout limit reached.">
                    <div class="determinate" style="background-color: #5bc0de; width: <?= $weiToEthpercent ?>%"></div>
                </div>
            </div>

        </div>
    </div>


    <div class="grid-example col s12 m6 l3">
        <div class="card blue-grey darken-1">

            <div class="card-action white-text center" style="background-color: #5cb85c">
                <h6>Active Workers</h6>
            </div>

            <div class="card-content white-text center">
                <h6><?= $ethermine->minerStats->activeWorkers ?></h6>
            </div>

        </div>
    </div>


    <div class="grid-example col s12 m6 l3">
        <div class="card blue-grey darken-1">

            <div class="card-action white-text center" style="background-color: #d9534f">
                <h6>Shares (Last 1h)</h6>
            </div>

            <div class="card-content white-text center">
                <h6>
                    <span class="tooltipped" data-position="bottom" data-delay="50"
                          data-tooltip="Valid shares"><?= $ethermine->minerStats->validShares ?> (<?= $shares_percent ?>
                        %)</span> -
                    <span class="tooltipped" data-position="bottom" data-delay="50"
                          data-tooltip="Stale shares"><?= $ethermine->minerStats->staleShares ?>
                        (<?= $stale_shares_percent ?>%)</span> -
                    <span class="tooltipped" data-position="bottom" data-delay="50"
                          data-tooltip="Invalid shares"><?= $ethermine->minerStats->invalidShares ?>
                        (<?= $invalid_shares_percent ?>%)</span>
                </h6>
            </div>
        </div>
    </div>

</div>

<br>

<table class="striped centered responsive-table" style="font-size: smaller">
    <thead>
    <tr class="flow-text">
        <th>Worker</th>
        <?php if ($ethermine->reportedHashRate > 0) { echo '<th>Reported HashRate</th>'; } ?>
        <th>Current Hashrate</th>
        <th>Valid Shares (1h)</th>
        <th>Stale Shares</th>
        <th>Invalid Shares</th>
        <th>Last Seen</th>
    </tr>
    </thead>

    <tbody>

    <?php
    //print_r($ethermine->workers);
    foreach ($ethermine->workers as $key => $item) {

        $realDate = date('Y-m-d H:i:s', $item->workerLastSubmitTime);

        # Get 100% shares
        $total = $item->validShares + $item->staleShares + $item->invalidShares;

        # Calculate shares in %
        $shares_percent = round($item->validShares / $total * 100, 0);

        # Calculate stale shares in %
        $stale_shares_percent = round($item->staleShares / $total * 100, 0);

        # Calculate invalid shares in %
        $invalid_shares_percent = round($item->invalidShares / $total * 100, 0);

        echo '<tr class="flow-text">';
        echo '<td>' . $item->worker . '</td>';
        if ($item->reportedHashRate > 0)
        {
            echo '<td>' . $item->reportedHashRate . '</td>';
        }
        echo '<td>' . $item->hashrate . '</td>';
        echo '<td>' . $item->validShares . ' ('. $shares_percent .'%)</td>';
        echo '<td>' . $item->staleShares . ' ('. $stale_shares_percent .'%)</td>';
        echo '<td>' . $item->invalidShares . ' (' . $invalid_shares_percent . '%)</td>';
        echo '<td><div class="workerLastSubmitTime" datetime="' . $realDate . '"></div></td>';
        echo '</tr>';

    }
    ?>

    </tbody>
</table>

<a target="_blank" href="https://ethermine.org/miners/<?= $wallet ?>">ehtermine.org statistics</a>

<?php } else { ?>

    To get the stats from ethermine Dashboard just enter your wallet to the url query.<br>Example: https://phpecho.de/dual_dashboard/index.php?wallet=<b>WALLET</b>&sub=subdomain

<?php } ?>

<br><br>

<div class="row center">
    <h5 class="header col s12 light">ethOS Dashboard</h5>
</div>


<?php if(isset($urlID) AND !empty($urlID)) { ?>

<br>

<?php
$alive_gpus = $ethosdistro->alive_gpus;
$total_gpus = $ethosdistro->total_gpus;

if ($alive_gpus == $total_gpus) {
    $gpu_color = "green";
} else {
    $gpu_color = "red";
}

if ($ethosdistro->avg_temp > 75) {
    $avgtemp_color = "red";
} else {
    $avgtemp_color = "green";
}

$alive_rigs = $ethosdistro->alive_rigs;
$total_rigs = $ethosdistro->total_rigs;

if ($alive_rigs === $total_rigs) {
    $rigs_color = "green";
} else {
    $rigs_color = "red";
}

foreach ($ethosdistro->rigs as $key => $item) {
    $totalWatts = 0;
    $watts .= $item->watts . ' ';
    $total_watts = explode(" ", $watts);
    foreach ($total_watts as $watt) {
        $totalWatts += $watt;
    }
}
?>

<table class="striped centered" style="font-size: smaller">

    <tbody>
    <tr class="flow-text">
        <td><b><?= $ethosdistro->total_hash ?> MH/s</b></td>
        <td><span style="color: <?= $gpu_color ?>"><?= $alive_gpus ?>/<?= $total_gpus ?></span> gpus
            (<?= $ethosdistro->capacity ?>%)
        </td>
        <td><span style="color: <?= $avgtemp_color ?>"><?= round($ethosdistro->avg_temp, 2, PHP_ROUND_HALF_UP); ?></span> °C (avg)</td>
        <td><span style="color: <?= $rigs_color ?>"><?= $ethosdistro->alive_rigs ?>/<?= $ethosdistro->total_rigs ?></span> rigs</td>
        <?php /*<td>Latest Version: <a target="_blank" href="http://ethosdistro.com/changelog/"><?= $ethosdistro->current_version ?></a></td> */ ?>
        <td><?= $totalWatts ?>W</td>
    </tr>
    </tbody>

</table>

<br>

    <ul class="collapsible popout" data-collapsible="accordion">
        <li>
            <div class="collapsible-header active"><i class="material-icons"><img style="max-width: 20px;" src="img/ethos_icon.png"></i>Statistics</div>
            <div class="collapsible-body">
                <table class="striped responsive-table bordered centered" style="font-size: smaller">
                    <thead class="flow-text">
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Hash</th>
                        <th>GPUs</th>
                        <th>Temp Ø</th>
                        <th>Watts</th>
                        <?php /* <th>Last Ping</th> */ ?>
                    </tr>
                    </thead>

                    <tbody>

                    <?php

                    foreach ($ethosdistro->rigs as $key => $item) {

                        if ($item->miner === "claymore") {
                            $miner = "cl";
                        } elseif ($item->miner === "ethminer") {
                            $miner = "et";
                        } else {
                            $miner = $item->miner;
                        }


                        /*
                        just_booted
                        mining
                        unreachable
                        no_hash
                         */

                        $tr_class = "";
                        if ($item->condition === "no_hash" OR $item->condition === "unreachable") {
                            $tr_class = "#ebcccc"; // #ACEB64
                        }

                        if ($item->condition === "mining") {
                            $min_colr = "#ACEB64";
                        } elseif ($item->condition === "just_booted") {
                            $min_colr = "#E2E2E2";
                        } elseif ($item->condition === "unreachable") {
                            $min_colr = "#ebcccc";
                        } elseif ($item->condition === "no_hash") {
                            $min_colr = "#ebcccc";
                        } elseif ($item->condition === "throttle") {
                            $min_colr = "#ebcccc";
                        }  elseif ($item->condition === "high_load") {
                            $min_colr = "#ebcccc";
                        }


                        $alive_gpus = $item->gpus;
                        $total_gpus = $item->miner_instance;

                        if ($alive_gpus == $total_gpus) {
                            $gpu_color = "green";
                        } else {
                            $gpu_color = "red";
                        }

                        if ($total_gpus === 0) {
                            $percent = 0;
                        } else {
                            $percent = number_format($alive_gpus / $total_gpus * 100, 0, '.', '');
                        }


                        // AVG gpu temp by rig
                        $avg_result = 0;
                        $expl_temp = explode(" ", $item->temp);
                        $ceil = count($expl_temp);
                        foreach ($expl_temp as $ints) {
                            $avg_result += $ints / $ceil;
                            #$avg_result += round($ints / $ceil, 0, PHP_ROUND_HALF_UP);
                        }

                        #$avg_temp = number_format($avg_result, 1);
                        $avg_temp = round($avg_result, 2, PHP_ROUND_HALF_UP);


                        if ($avg_temp >= 85) {
                            $avg_temp_colr = "red";
                        } else {
                            $avg_temp_colr = "green";
                        }

                        $totalWatts = 0;
                        $watts = $item->watts . ' ';
                        $total_watts = explode(" ", $watts);
                        foreach ($total_watts as $watt) {
                            $totalWatts += $watt;
                        }


                        echo '<tr class="flow-text" style=" background-color: ' . $tr_class . '">';

                        echo '<td>' . $item->rack_loc . '</td>';
                        echo '<td> <span style="background-color: '.$min_colr.'"> ' . $item->condition . '</span></td>';
                        echo '<td><b>' . $item->hash . ' MH/s</b></td>';
                        echo '<td><span style="color: '. $gpu_color .'">'. $alive_gpus .'/'. $total_gpus .'</span> ('.$percent.'%)</td>';
                        echo '<td><span style="color: '.$avg_temp_colr.'"> '. $avg_temp .'</span>°C</td>';
                        echo '<td> '. $totalWatts .'W</td>';
                        #echo '<td><div class="rendered_uptime" datetime="' . date('Y-m-d H:i:s', $item->server_time) . '"></div></td>';
                        echo '</tr>';
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </li>
        <li>
            <div class="collapsible-header"><i class="material-icons"><img style="max-width: 20px;" src="img/ethos_icon.png"></i>Detail Statistics</div>
            <div class="collapsible-body">
                <table class="striped responsive-table centered bordered" style="font-size: smaller">
                    <thead>
                    <tr>
                        <th><span class="tooltipped" data-position="bottom" data-delay="50"
                                  data-tooltip="ethOS Version & miner">V</span></th>
                        <th><span class="tooltipped" data-position="bottom" data-delay="50"
                                  data-tooltip="current running miner">M</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="live GPUs / dedected GPUs,<br> hover to get gpu model names">G</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50" data-tooltip="rig name">name</span>
                        </th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="ailment, condition, event that affects rig,<br> hover over for more info">a</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="location of rig, check ethosdistro.com/pool.txt for sample config">loc</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="rig IP address and admin terminal, <br>green = fglrx, blue = amdgpu">rig admin</span>
                        </th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="GPU driver">D</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="elapsed time since rig lasted pinged your stats panel (last reachable)">p</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="5 minute load average (sysload)">L</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="cpu temperature (in C)">C</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="amount of system ram (in gigabytes)">R</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="total free space (in gigabytes)">F</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="total hashrate, hover over to see local pool info,<br> hash color will be odd if pool info is different">H</span>
                        </th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="hashrate per GPU">hashes</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="temperature of GPUs (in C), click on temps<br> to see historical data">temps</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="FGLRX = powertune, AMDGPU = dpm state, <br>NVIDIA = performance level state, <br>hover to see gpu voltages">ptune</span>
                        </th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="gpu estimated watts (currently only available for NVIDIA and AMDGPU)">watts</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="GPU fan rpms (in K-rpm), click on fan rpms to see<br> historical data, hover to see percents">fans</span>
                        </th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="gpu core clocks (in ghz), hover to get default GPU core clocks">core</span></th>
                        <th><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50"
                                  data-tooltip="gpu memory clocks (in ghz), hover to get default GPU mem clocks">mem</span></th>

                    </tr>
                    </thead>

                    <tbody>

                    <?php

                    foreach ($ethosdistro->rigs as $key => $item) {

                        if ($item->miner === "claymore") {
                            $miner = "cl";
                        } elseif ($item->miner === "ethminer") {
                            $miner = "et";
                        } else {
                            $miner = $item->miner;
                        }


                        /*
                        just_booted
                        mining
                        unreachable
                        no_hash
                         */

                        $tr_class = "";
                        if ($item->condition === "no_hash" OR $item->condition === "unreachable") {
                            $tr_class = "#ebcccc"; // #ACEB64
                        }

                        if ($item->condition === "mining") {
                            $min_colr = "#ACEB64";
                        } elseif ($item->condition === "just_booted") {
                            $min_colr = "#E2E2E2";
                        } elseif ($item->condition === "unreachable") {
                            $min_colr = "#ebcccc";
                        } elseif ($item->condition === "no_hash") {
                            $min_colr = "#ebcccc";
                        } elseif ($item->condition === "throttle") {
                            $min_colr = "#ebcccc";
                        }  elseif ($item->condition === "high_load") {
                            $min_colr = "#ebcccc";
                        }

                        echo '<tr style=" background-color: ' . $tr_class . '">';
                        echo '<td>' . $item->version . '</td>';
                        echo '<td>' . $miner . '</td>';
                        echo '<td><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50" data-tooltip="' . $item->meminfo . '<br>">' . $item->gpus . '/' . $item->miner_instance . '</span></td>';
                        echo '<td><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50" data-tooltip="' . $item->hash . ' MH/s<br> ' . $item->mobo . ' <br> ' . $item->drive_name . ' <br> ' . $item->lan_chip . '<br>">' . $key . '</span></td>';
                        echo '<td><span class="tooltipped" data-html="true" data-position="bottom" data-delay="50" data-tooltip="' . $item->condition . '"><span style="background-color: '.$min_colr.'">' . $item->condition . '</span></td>';
                        echo '<td>' . $item->rack_loc . '</td>';
                        echo '<td><a target="_blank" href="http://' . $item->ip . '">' . $item->ip . '</a></td>';
                        echo '<td>' . $item->driver . '</td>';
                        echo '<td><div class="rendered_uptime" datetime="' . date('Y-m-d H:i:s', $item->server_time) . '"></div></td>';
                        echo '<td><a target="_blank" href="http://' . $urlID . '.ethosdistro.com/graphs/?rig=' . $key . '&type=load_rx_tx">' . $item->load . '</a></td>';
                        echo '<td><a target="_blank" href="http://' . $urlID . '.ethosdistro.com/graphs/?rig=' . $key . '&type=cpu_temp">' . $item->cpu_temp . '</a></td>';
                        echo '<td>' . $item->ram . '</td>';
                        echo '<td>' . $item->freespace . '</td>';
                        echo '<td><b>' . $item->hash . ' MH/s</b></td>';
                        echo '<td>' . $item->miner_hashes . '</td>';
                        echo '<td><a target="_blank" href="http://' . $urlID . '.ethosdistro.com/graphs/?rig=' . $key . '&type=temp">' . $item->temp . '</a></td>';
                        echo '<td>' . $item->powertune . '</td>';
                        echo '<td><a target="_blank" href="http://' . $urlID . '.ethosdistro.com/graphs/?rig=' . $key . '&type=watts">' . $item->watts . '</a></td>';
                        echo '<td><a target="_blank" href="http://' . $urlID . '.ethosdistro.com/graphs/?rig=' . $key . '&type=fanrpm">' . $item->fanrpm . '</a></td>';
                        echo '<td><a target="_blank" href="http://' . $urlID . '.ethosdistro.com/graphs/?rig=' . $key . '&type=core">' . $item->core . '</a></td>';
                        echo '<td><a target="_blank" href="http://' . $urlID . '.ethosdistro.com/graphs/?rig=' . $key . '&type=mem">' . $item->mem . '</a></td>';
                        echo '</tr>';

                    }
                    ?>

                    </tbody>
                </table>

                <br>
                <a style="font-size: smaller" target="_blank" href="http://<?= $urlID ?>.ethosdistro.com">http://<?= $urlID ?>.ethosdistro.com</a>
            </div>
        </li>
        <li>
            <div class="collapsible-header"><i class="material-icons"><img style="max-width: 20px" src="img/ethermine_icon.png"> </i>Payouts</div>
            <div class="collapsible-body">
                <table class="striped responsive-table centered bordered" style="font-size: smaller">
                    <thead>
                    <tr>
                        <th>Paid on</th>
                        <th>From Block</th>
                        <th>To Block</th>
                        <?php /*<th>Duration [h]</th> */ ?>
                        <th>Amount</th>
                        <th>Tx</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php

                    $totalEther = 0;
                    foreach ($ethermine->payouts as $key => $item) {

                        /*
                        $date1 = new DateTime("2007-03-24");
                        $date2 = new DateTime("2009-06-26");
                        $interval = $date1->diff($date2);
                        #echo "difference " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days ";
                        // shows the total amount of days (not divided into years, months and days like above)
                        #echo "difference " . $interval->days . " days ";
                        */

                        $totalEther = 0;
                        $mined_ether .= $item->amount . ' ';
                        $total_mined_ether = explode(" ", $mined_ether);
                        foreach ($total_mined_ether as $ether) {
                            $totalEther += $ether;
                        }

                        $time = strtotime($item->paidOn);

                        echo '<tr>';
                        echo '<td>'. date('Y-m-d H:i',$time) .'</td>';
                        echo '<td>'. $item->start .'</td>';
                        echo '<td>'. $item->end .'</td>';
                        echo '<td>'. weiToEther($item->amount) .'</td>';
                        echo '<td><a target="_blank" href="http://etherchain.org/tx/'. $item->txHash .'">'. $item->txHash .'</a></td>';
                        echo '</tr>';
                    }

                    # total payouts usd balance
                    setlocale(LC_MONETARY, 'en_US');
                    echo 'Total Payouts: <b>' .weiToEther($totalEther) . ' ETH</b> ($' .$total_payout = money_format('%i', weiToEther($totalEther) * $coinmktcap[0]->price_usd) . ')';

                    ?>

                    </tbody>
                </table>
            </div>
        </li>
    </ul>

    <?php } else { ?>

    To get the stats from ethOS Dashboard just enter your subdomain from ethosdistro.com to the url query.<br>Example: https://phpecho.de/dual_dashboard/index.php?wallet=WALLET&sub=<b>subdomain</b>

    <?php } ?>

</div>

<br><br>

<!--  Scripts-->
<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="js/materialize.js"></script>
<script src="js/timeago.js"></script>
<script src="js/timeago.locales.min.js"></script>
<script src="js/init.js"></script>

</body>
</html>
