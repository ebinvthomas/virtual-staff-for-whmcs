<?php
function auto_mail_ucartz_config() {
    $configarray = array(
        "name" => "Virtual staff",
        "description" => "This module used to generate automated ticket and replies depends on invoice status",
        "version" => "1.1",
        "author" => "Ucartz Online Pvt Ltd",
        "fields" => array(
            "option1" => array ("FirstNotification" => "Option1", "Type" => "text", "Size" => "2", "Description" => "Number of days for first notification after invoice creation", "Default" => "7" ),
            "option2" => array ("SecondNotification" => "Option2", "Type" => "text", "Size" => "2", "Description" => "Number of days for second notification after invoice creation", "Default" => "10" ),
            "option3" => array ("ThirdNotification"=> "Option3", "Type" => "text", "Size" => "2", "Description" => "Number of days for third notification after invoice creation", "Default" => "12" ),
            "option4" => array ("FourthNotification" => "Option4", "Type" => "text", "Size" => "2", "Description" => "Number of days for fourth notification after invoice creation", "Default" => "15" ),
            "option5" => array ("ThankyouTemplate" => "Option5", "Type" => "textarea", "Rows" => "15", "Cols" => "30", "Description" => "Thank You Template", "Default" => "Hello, \n\nThank you for payment" ),
            "option6" => array ("NotificationOneTemplate" => "Option6", "Type" => "textarea", "Rows" => "15", "Cols" => "30", "Description" => "Notification One Template", "Default" => "Hello, \n\nThis is payment notification" ),
            "option7" => array ("NotificationTwoTemplate" => "Option6", "Type" => "textarea", "Rows" => "15", "Cols" => "30", "Description" => "Notification Two Template", "Default" => "Hello, \n\nThis is second notification" ),
            "option8" => array ("NotificationThreeTemplate" => "Option6", "Type" => "textarea", "Rows" => "15", "Cols" => "30", "Description" => "Notification Three Template", "Default" => "Hello, \n\nThis is third notification" ),
            "option9" => array ("NotificationFourTemplate" => "Option6", "Type" => "textarea", "Rows" => "15", "Cols" => "30", "Description" => "Notification Four Template", "Default" => "Hello, \n\nThis is last notification" ),
            "option10" => array ("Department" => "Option10", "Type" => "text", "Size" => "2", "Description" => "Department Id for default ticket creation", "Default" => "1" ),
            "option11" => array ("Subject" => "Option11", "Type" => "text", "Size" => "30", "Description" => "Subject for ticket", "Default" => "Invoice Notification" )
        )
    );
    return $configarray;
}

function auto_mail_ucartz_activate() {

    # Create Custom DB Table
    $query = 'CREATE TABLE `mod_automail` ( `id` INT(15) NOT NULL AUTO_INCREMENT , `invoice_id` INT(15) NOT NULL , `ticket_id` INT(15) NOT NULL , `notifications` INT(5) NOT NULL DEFAULT 0, PRIMARY KEY (`id`)) ENGINE = InnoDB;';
	$result = full_query($query);

    # Return Result
    if($result) {
        return array('status'=>'success','description'=>'Thank you for activating module by Ucartz Online Pvt Ltd');
    }
    else {
        return array('status'=>'error','description'=>'Error in installing module');
    }
}

function auto_mail_ucartz_deactivate() {

    # Remove Custom DB Table
    $query = "DROP TABLE `mod_automail`";
	  $result = full_query($query);

    # Return Result
    if($result) {
        return array('status'=>'success','description'=>'Module deactivated.. :(');
    }
    else {
        return array('status'=>'error','description'=>'Error in clearing data.');
    }
}
