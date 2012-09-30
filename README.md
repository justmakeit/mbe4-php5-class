mbe4-php5-class
===============

Easy use of the mbe4 mobile payment api.

Development sponsored by just make IT! (http://www.justmakeit.de)

Example
===============

```php
// Create new mbe4-object
$mbe4obj = new mbe4($username, $password, $clientid, $serviceid, $url);
// Returns key/value-array with all needed parameters for GET-Redirect.
$data = $mbe4obj->create_transaction($id,$description, $amount, $contentclass=1, $returnurl,$urlencode);
// Check the returned GET-parameters, called at the returnurl
$result = $mbe4obj->validate_transaction($_GET, $password);
if($mbe4obj->is_valid_responsecode($result))
    echo "Transaction finished."
else
    echo "Error ". $result.": " . $mbe4obj->get_responsemsg_by_responsecode($result);
```

mbe4 mobile payment widget reference
====================================
If you need some informations about the mbe4-specifications, please take a look at http://www.scribd.com/doc/50699085/Anlage3-mbe4-Spezifikation-Mobile-Payment-Widget-v2-2