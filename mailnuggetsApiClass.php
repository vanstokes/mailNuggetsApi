<?php
########################################################
##	
##	Class for calling the mailnuggets.com API 
##	Van Stokes
##	10.07.2011
##	v0.03
##	
##	This class demonstrates the MailNuggets API
##	
##	MANAGE TEMPORARY EMAIL ADDRESSES
##	
##	Temporary receiving-only email addresses can be 
##	created for your MailNuggets account.  These email 
##	addresses (also referred to as 'throwaways') send email
##	to your remote script via a mailnuggets rule.  When you
##	no longer want that address to send email to your 
##	script, you can delete that address with this API.
##
##	GET FEED OF EMAILS FROM LAST 48 HOURS
##	
##	You can access an XML feed of email_id's, and with information
##	for each email received from the last 48 hours.
##	
##	REPOST EMAILS
##	
##	You can trigger any email message be rePOSTed to your remote script.
##	For example, if you list a feed of emails over the last 48 hours, and 
##	find an email that is not stored in your database, you can trigger it
##	to be rePOSTed.
##	
##	NOTES:
##
##	- Throwaways are always stored as lower case, and must be
##	  alphanumeric	
##
##	- You will need curl() and hash_mac() to use this script
##	
##	- The API returns XML, and meaningful errors
##
########################################################

// general debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if(!function_exists('hash_hmac'))
	{
		echo 'Error: You need hash_mac installed with PHP to use the API <br/>';
	}

if(!function_exists('curl_init'))
	{
		echo 'Error: You need curl installed with PHP to use the API <br/>';
	}

class mailNuggets {
	
	/***
	*	Replace the values below with information from your 
	*	mailnuggets account at: https://www.mailnuggets.com/usersettings
	*/

	var $apiUserId = '123';
	var $apiKey = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX';
	var $apiSecretKey = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX';
	
	
	
	
	// You can leave everything below as is
	var $apiEndPoint = 'https://www.mailnuggets.com/users/api/';
	
	function makeCurlRequest($url)
		{
		
		// sign request
		$signature = hash_hmac('sha256', $url, $this->apiSecretKey);
		$url .= "&SIGNATURE=$signature";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		return curl_exec($ch);

		}
		
	/***
	*	The order of the query string parameters is important.  The API may not work if 
	*	they are re-ordered
	*/	
	
	// Manage temporary email addresses (throwaways)
	
	function listAllThrowaways()
	 	{
			$url = $this->apiEndPoint . "listall?APIUSERID=". $this->apiUserId ."&APIKEY=". $this->apiKey;
			return $this->makeCurlRequest($url);
		}

	function lookupThrowaway($throwawayName)
		{
			$url = $this->apiEndPoint . "lookup?APIUSERID=". $this->apiUserId ."&APIKEY=". $this->apiKey."&THROWAWAYNAME=$throwawayName";
			return $this->makeCurlRequest($url);
		}

	function addThrowaway($throwawayName = null)
		{
			$url = $this->apiEndPoint . "add?APIUSERID=". $this->apiUserId ."&APIKEY=". $this->apiKey."&THROWAWAYNAME=$throwawayName";
			return $this->makeCurlRequest($url);
		}	

	function removeThrowaway($throwawayName)
		{
			$url = $this->apiEndPoint . "remove?APIUSERID=". $this->apiUserId ."&APIKEY=". $this->apiKey."&THROWAWAYNAME=$throwawayName";
			return $this->makeCurlRequest($url);
		}
		
	// List feed of key email information from last 48 hours
	
	function listEmails($emailId = null)
		{
			$url = $this->apiEndPoint . "listemails?APIUSERID=". $this->apiUserId ."&APIKEY=". $this->apiKey."&EMAILID=$emailId";
			return $this->makeCurlRequest($url);
		}
	
	// RePOST an email to the original remote script
	
	function repostEmail($emailId)
		{
			$url = $this->apiEndPoint . "repostemail?APIUSERID=". $this->apiUserId ."&APIKEY=". $this->apiKey."&EMAILID=$emailId";
			return $this->makeCurlRequest($url);
		}
		
}

########################################################
##	How to use this class
########################################################

// First create a new instance of the mailNuggets class
$mailNuggets = new mailNuggets();

/***
 	List all throwaways 

	Example Response:
	<?xml version="1.0" encoding="UTF-8"?>
		<allthrowaways>
			<throwaway>
				<name>846543ef</name>
				<createddate>2010-11-23 03:31:44</createddate>
				<expiresdate>2999-10-26 00:00:00</expiresdate>
			</throwaway>
			<throwaway>
				<name>bb1eb24f</name>
				<createddate>2010-11-23 03:33:24</createddate>
				<expiresdate>2999-10-26 00:00:00</expiresdate>
			</throwaway>
		</allthrowaways>
*/
// echo $mailNuggets->listAllThrowaways();


/***
 	Add throwaways

	NOTES: 
	- The throwawayexamplename parameter is optional here.  If you include
	it, it must be alphanumeric and unique
	
	- If you don't include the throwawayexamplename parameter, a name will
	be automatically generated
	
	- The response contains the name whether it was generated or added
	as a parameter 
	
	Example Response:
	<?xml version="1.0" encoding="UTF-8"?>
		<success>
			<throwaway>
				<name>throwawayexamplename</name>
			</throwaway>
		</success>
*/
// echo $mailNuggets->addThrowaway('throwawayexamplename');


/***
 	Lookup a single throwaway
	
	Example Response:
	<?xml version="1.0" encoding="UTF-8"?>
		<throwaway>
			<name>throwawayexamplename</name>
			<createddate>2010-11-23 07:44:58</createddate>
			<expiresdate>2999-10-26 00:00:00</expiresdate>
		</throwaway>
*/
// echo $mailNuggets->lookupThrowaway('throwawayexamplename');


/***
	Remove a throwaway
	
	Example Response:
	<?xml version="1.0" encoding="UTF-8"?>
		<success>1</success>
*/
// echo $mailNuggets->removeThrowaway('throwawayexamplename');

/***
	List emails from past 48 hours
	
	NOTES: 
	- The emailid parameter is optional here.  If given, it returns all emails received after that emailid.  The emailid parameter is proprietary to MailNuggets.
	
	- Does not return email message or attachment information
	
	- HTML special characters are escaped, so for example, '<' becomes '&lt;' and '>' becomes '&gt;'
	
	
	Example Response:
	<?xml version="1.0" encoding="UTF-8"?>
		<emails>
			<email>
				<emailid>123456542</emailid>
				<subject>Example email 1</subject>
				<messageid>CAGjVw=r7mNKN8kyJFucGscMFdVRQLudrvd2NfLDC3ccQQDv7=Q@mail.gmail.com</messageid>
				<from>Van Stokes &lt;mail@vanstokes.com&gt;</from>
				<to>mail@apitester.mailnuggets.com</to>
				<hasattachment>0</hasattachment>
				<createdtimestamp>1317955927</createdtimestamp>
				<postsent>0</postsent>
				<postresponse></postresponse>
			</email>
		</emails>
*/

// echo $mailNuggets->listEmails();

/***
	Repost an email
	
	NOTES: 
	- The emailid parameter is required.
	
	- Only works on emails less than two days old
	
	- Posts to the original remote script, in the original format
	
	Example Response:
	<?xml version="1.0" encoding="UTF-8"?>
		<success>
			<email>
				<emailid>56135</emailid>
			</email>
		</success>

*/

// echo $mailNuggets->repostEmail(123456);

/***
	NOTE: Errors are returned like so:
	<?xml version="1.0" encoding="UTF-8"?>
		<errors>
			<error> Example error message </error>
		</errors>

*/



?>