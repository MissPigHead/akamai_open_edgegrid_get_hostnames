1. Run get_cnameFrom.php
2. get_cnameFrom.php will fetch all the cname under 'https://.luna.akamaiapis.net/', then record the array of cnameFrom as cnameFrom_{date('Y-m-d')}.json.
3. If error occurs, the error message will be saved as {statusCode}_{reason}_{date('YmdHis')}.txt in the same directory as this file.
4. The average execution time is about 450 ~ 500 seconds. You may check the output file for your next step at 10 minutes after starting get_cnameFrom.php.