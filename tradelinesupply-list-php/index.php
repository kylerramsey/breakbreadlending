<?php

$consumer_key = 'ck_1a2f0119e577f0a99476c976b6eb2818400cdc1e'; // Change this value
$consumer_secret = 'cs_b0063ddb2ee6a84fa0b35bef91732f97b3d59462'; // Change this value

// Do not change from this line
$url = 'https://tradelinesupply.com/wp-json/wc/v3/pricing';
//$url = 'http://localhost:8080/wp-json/wc/v3/pricing/';


function join_with_equals_sign($params, $query_params = array(), $key = '')
{
  foreach ($params as $param_key => $param_value) {
    if ($key) {
      $param_key = $key . '%5B' . $param_key . '%5D'; // Handle multi-dimensional array.
    }

    if (is_array($param_value)) {
      $query_params = join_with_equals_sign($param_value, $query_params, $param_key);
    } else {
      $string = $param_key . '=' . $param_value; // Join with equals sign.
      $query_params[] = wc_rest_urlencode_rfc3986($string);
    }
  }

  return $query_params;
}

function wc_rest_urlencode_rfc3986($value)
{
  if (is_array($value)) {
    return array_map('wc_rest_urlencode_rfc3986', $value);
  }

  return str_replace(array('+', '%7E'), array(' ', '~'), rawurlencode($value));
}

$time = time();
$params = [
  'oauth_consumer_key' => $consumer_key,
  'oauth_nonce' => $time,
  'oauth_signature_method' => 'HMAC-SHA1',
  'oauth_timestamp' => $time,
];
$query = [];
foreach ($params as $key => $value) {
  $query[] = $key . '=' . $value;
}
$http_method = 'GET';
$base_request_uri = rawurlencode($url);
// Normalize parameter key/values.
//$params         = $this->normalize_parameters( $params );

$query_string = implode('%26', join_with_equals_sign($params)); // Join with ampersand.
$string_to_sign = $http_method . '&' . $base_request_uri . '&' . $query_string;
$hash_algorithm = strtolower(str_replace('HMAC-', '', $params['oauth_signature_method']));
$secret = $consumer_secret . '&';
$signature = base64_encode(hash_hmac($hash_algorithm, $string_to_sign, $secret, true));

$url = $url . "?" . implode('&', $query) . "&oauth_signature=" . $signature;

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_POSTFIELDS => "",
  CURLOPT_HTTPHEADER => array(
    "Postman-Token: 00cf5ba3-9d4e-466f-8af5-9cc4b621dd69",
    "cache-control: no-cache"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
  return;
}
$data = json_decode($response, JSON_OBJECT_AS_ARRAY);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Pricing Table</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script src="stupidtable.js?dev"></script>
  <style>
    table thead tr th {
      background-image: url('bg.png') !important;
      background-size: 10px;
      background-repeat: no-repeat;
      background-position: right center;
    }

    table thead tr th.sorting-desc {
      background-image: url('desc.png') !important;
    }

    table thead tr th.sorting-asc {
      background-image: url('asc.png') !important;
    }
  </style>
  <script>
    $(function () {
      $("table").stupidtable();
    });
  </script>
</head>

<body>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <table class="table">
        <thead>
        <tr>
          <th data-sort="int"></th>
          <th data-sort="string">Bank Name</th>
          <th data-sort="float" data-sort-onload="false">Card ID</th>
          <th data-sort="float" data-sort-onload="false">Credit Limit</th>
          <th data-sort="float" data-sort-onload="false">Date Opened</th>
          <th data-sort="float" data-sort-onload="false">Purchase By Date</th>
          <th data-sort="float" data-sort-onload="false">Reporting Date</th>
          <th data-sort="float" data-sort-onload="false">Stock</th>
          <th data-sort="float" data-sort-onload="yes">Price</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($data as $line) {
          ?>
          <tr>
            <td>
              <img src="<?php echo $line['image']; ?>" style="with: 50px"/>
            </td>
            <td><?php echo $line['bank_name']; ?></td>
            <td data-sort-value="<?php echo $line['card_id'];?>"><?php echo $line['card_id']; ?></td>
            <td data-sort-value="<?php echo $line['credit_limit_original']; ?>"><?php echo $line['credit_limit']; ?></td>
            <td data-sort-value="<?php echo $line['date_opened_original']; ?>"><?php echo $line['date_opened']; ?></td>
            <td data-sort-value="<?php echo $line['purchase_deadline_original']; ?>"><?php echo $line['purchase_deadline']; ?></td>
            <td data-sort-value="<?php echo $line['reporting_period_original']; ?>"><?php echo $line['reporting_period']; ?></td>
            <td data-sort-value="<?php echo $line['stock']; ?>"><?php echo $line['stock']; ?></td>
            <td data-sort-value="<?php echo $line['price']; ?>">$<?php echo $line['price']; ?></td>
          </tr>
          <?php
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>

</html>
