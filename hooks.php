<?php
/**
 * Register hook function call.
 * 
 * Name: Virtual staff for WHMCS
 * Plugin: WHMCS extention addon module
 * Author: Ebin V Thomas (Ucartz Online Pvt Ltd)
 *
 * @param string $hookPoint The hook point to call
 * @param integer $priority The priority for the given hook function
 * @param string|function Function name to call or anonymous function.
 *
 * @return Depends on hook function point.
 */

define('Ucartz_User', 'Jarvis');

add_hook('AfterCronJob', 1, function ($var) {
    // Get Invocies
    $vars = automail_get_config();
    $invocies = automail_get_invoices('Unpaid');
    $invoice_notified = automail_get_notifications('invoice_id');
    $inv_count = count($invocies);
    if($invocies) {
        for($i=0;$i<$inv_count;$i++) {
            //var_dump($invocies[$i]);
            $invoice_id = $invocies[$i]["id"];
            $date1=date_create($invocies[$i]["date"]);
            $date2=date_create(date("Y-m-d"));
            $diff=date_diff($date1,$date2);
            $date_diff = $diff->format("%a");
            
            //echo "$invoice_id - $date_diff";
            //var_dump($vars);
            
            $client = automail_get_client($invocies[$i]['userid']);
            
            $message = '';
            if(!isset($invoice_notified[$invoice_id])) {
                automail_add_notification($invoice_id, 0, $vars["option1"]);
            }
            else {
                if($invoice_notified[$invoice_id]['notifications'] == $vars["option1"] && $date_diff == $vars["option1"]) {
                    $message = $vars['option6'];
                    $next = $vars["option2"];
                }
                elseif($invoice_notified[$invoice_id]['notifications'] == $vars["option2"] && $date_diff == $vars["option2"]) {
                    $message = $vars['option7'];
                    $next = $vars["option3"];
                }
                elseif($invoice_notified[$invoice_id]['notifications'] == $vars["option3"] && $date_diff == $vars["option3"]) {
                    $message = $vars['option8'];
                    $next = $vars["option4"];
                }
                elseif($invoice_notified[$invoice_id]['notifications'] == $vars["option4"] && $date_diff == $vars["option4"]) {
                    $message = $vars['option9'];
                    $next = 100;
                }
                //echo ' ~ '.$next.' ~ ';
                if($message != '') {
                    $message = str_replace(array('%%FIRSTNAME%%', '%%LASTNAME%%', '%%COMPANY%%', '%%DUEDATE%%', '%%AMOUNT%%', '%%CURRENCY%%', '%%INVOICEDATE%%', '%%INVOICEID%%', '%%EMAIL%%' ), array($client['firstname'], $client['lastname'], $invocies[$i]["companyname"], $invocies[$i]["duedate"], $invocies[$i]["total"], $invocies[$i]["currencycode"], $invocies[$i]["date"], $invocies[$i]["id"], $client['email']), $message);
                    $subject = str_replace(array('%%FIRSTNAME%%', '%%LASTNAME%%', '%%COMPANY%%', '%%DUEDATE%%', '%%AMOUNT%%', '%%CURRENCY%%', '%%INVOICEDATE%%', '%%INVOICEID%%', '%%EMAIL%%' ), array($client['firstname'], $client['lastname'], $invocies[$i]["companyname"], $invocies[$i]["duedate"], $invocies[$i]["total"], $invocies[$i]["currencycode"], $invocies[$i]["date"], $invocies[$i]["id"], $client['email']), $vars['option11']);
                    if(isset($invoice_notified[$invoice_id]['ticket_id']) && $invoice_notified[$invoice_id]['ticket_id'] != 0) {
                        $ticket = automail_reply_ticket($invoice_notified[$invoice_id]['ticket_id'], $message, $client['firstname'].' '.$client['lastname'], $client['email']);
                        if($invoice_notified[$invoice_id]['notifications'] != 0) {
                            automail_update_notification($invoice_id, $next);
                        }
                    }
                    else {
                        $ticket = automail_open_ticket($vars['option10'], $subject, $message, $client['id'], $client['firstname'].' '.$client['lastname'], $client['email']);
                        automail_update_notification($invoice_id, $next, $ticket['id']);
                    }
                }
            }
        }
    }
});

add_hook('InvoicePaid', 1, function($var) {
    $invoice_id = $var['invoiceid'];
    $invoice = automail_get_invoice($invoice_id);
    $client = automail_get_client($invoice['userid']);
    $vars = automail_get_config();
    // Create or reply Thank you message ticket
    $invoice_notified = automail_get_notifications('invoice_id');
    $message = str_replace(array('%%FIRSTNAME%%', '%%LASTNAME%%', '%%COMPANY%%', '%%DUEDATE%%', '%%AMOUNT%%', '%%CURRENCY%%', '%%INVOICEDATE%%', '%%INVOICEID%%', '%%EMAIL%%' ), array($client['firstname'], $client['lastname'], $invocie["companyname"], $invocie["duedate"], $invocie["total"], $invocie["currencycode"], $invocie["date"], $invocie["id"], $client['email']), $vars['option5']);
    $subject = str_replace(array('%%FIRSTNAME%%', '%%LASTNAME%%', '%%COMPANY%%', '%%DUEDATE%%', '%%AMOUNT%%', '%%CURRENCY%%', '%%INVOICEDATE%%', '%%INVOICEID%%', '%%EMAIL%%' ), array($client['firstname'], $client['lastname'], $invocies[$i]["companyname"], $invocies[$i]["duedate"], $invocies[$i]["total"], $invocies[$i]["currencycode"], $invocies[$i]["date"], $invocies[$i]["id"], $client['email']), $vars['option11']);
    if(isset($invoice_notified[$invoice_id])) {
        if($invoice_notified[$invoice_id]['ticket_id'] && $invoice_notified[$invoice_id]['ticket_id'] != 0) {
            $ticket = automail_reply_ticket($invoice_notified[$invoice_id]['ticket_id'], $message, $client['firstname'].' '.$client['lastname'], $client['email'], 'Closed');
            automail_update_notification($invoice_id, 100);
        }
        else {
            //$ticket = automail_open_ticket($vars['option10'], 'Thank you for making payment', $message, $client['id'], $client['firstname'].' '.$client['lastname'], $client['email']);
            automail_update_notification($invoice_id, 101, $ticket['id']);
        }
    }
    else {
        //$ticket = automail_open_ticket($vars['option10'], 'Thank you for making payment', $message, $client['id'], $client['firstname'].' '.$client['lastname'], $client['email']);
        automail_add_notification($invoice_id, $ticket['id'], 101);
    }
});

function automail_get_config() {
    $settings = array();
    $q = @mysql_query("SELECT * FROM tbladdonmodules WHERE module = 'auto_mail_ucartz'");
    while ($arr = mysql_fetch_array($q)) {
        $settings[$arr['setting']] = html_entity_decode($arr['value']);
    }
    return $settings;
}

function automail_get_notifications($key) {
    $query = 'SELECT *  from `mod_automail`'; // where notifications < 4
    //$notifi = select_query('mod_automail', '*', array());
    $q = mysql_query($query);
    
    $invoice_notified = array();
    
    if($q) {
        while($notified = mysql_fetch_array($q)) {
            $invoice_notified[$notified[$key]] = $notified;
        }
    }
    return $invoice_notified;
}

function automail_get_client($client_id) {
    $command = 'GetClientsDetails';
    $postData = array(
        'clientid' => $client_id
    );
    $adminUsername = Ucartz_User;
    
    $results = localAPI($command, $postData, $adminUsername);
    return $results;
}

function automail_open_ticket($department, $subject, $message, $client_id, $cname, $cemail) {
    $name = Ucartz_User;
    $email = "noreply@ucartz.com";
    $command = 'OpenTicket';
    $postData = array(
      'deptid' => $department,
      'subject' => $subject,
      'message' => $message,
      'clientid' => $client_id,
      'name' => $name,
      'email' => $email,
      'admin' => true
    );
    $adminUsername = Ucartz_User;

    $results = localAPI($command, $postData, $adminUsername);
    return $results;
}

function automail_add_notification($invoice_id, $ticket_id, $notify=0) {
    $query = 'INSERT INTO `mod_automail` (`invoice_id`, `ticket_id`, `notifications`) values ("'.$invoice_id.'", "'.$ticket_id.'", "'.$notify.'")';
    return mysql_query($query);
}

function automail_update_notification($invoice_id, $inc, $ticket_id=0) {
    $query = 'UPDATE `mod_automail` set notifications='.$inc.($ticket_id==0?'':', ticket_id='.$ticket_id).' where invoice_id ="'.$invoice_id.'"';
    return mysql_query($query);
}

function automail_reply_ticket($ticket_id, $message, $name, $email, $status='') {
    $name = Ucartz_User;
    $email = "noreply@ucartz.com";
    $command = 'AddTicketReply';
    $postData = array(
        'ticketid' => $ticket_id,
        'message' => $message,
        'adminusername' => $name,
        'email' => $email,
        'noemail' => false
    );
    if($status != '') {
        $postData['status'] = $status;
    }
    $adminUsername = Ucartz_User;

    $results = localAPI($command, $postData, $adminUsername);
    return $results;
}

function automail_get_invoices($status) {
    $command = 'GetInvoices';
    $postData = array(
        'status' => $status,
    );
    $adminUsername = Ucartz_User;

    $results = localAPI($command, $postData, $adminUsername);
    return $results["invoices"]["invoice"];
}

function automail_get_invoice($invoice_id) {
    $command = 'GetInvoice';
    $postData = array(
        'invoiceid' => $invoice_id
    );
    $adminUsername = Ucartz_User;
    
    $results = localAPI($command, $postData, $adminUsername);
    return $results;
}

function automail_get_template($params) {
    $command = 'GetEmailTemplates';
    $postData = array(
        'type' => 'general'
    );
    $adminUsername = Ucartz_User;

    $results = localAPI($command, $postData, $adminUsername);
    return $results;
}
