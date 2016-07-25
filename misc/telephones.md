===========================================================================================
									 DESNI KUT GORE
===========================================================================================
# 1 (877) 512 1195
## US
TYPE TOLL-FREE Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

# 1 (877) 512 1195
## CANADA
TYPE TOLL-FREE Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

#+44 800 011 9648
## UK
TYPE TOLL-FREE Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

#+33 9 75 18 41 68
## FRANCE
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_treeFR/call_handler.php

#+34 518 88 82 27
## SPANISH
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_treeES/call_handler.php

#+39 06 9480 3714
## ITALY
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

#49 157 3598 1132
## GERMANY
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_treeDE/call_handler.php

#+61 8 7100 1671
## AUSTRALIA
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

#+31 85 888 5243
# NETHERLANDS
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

#+32 2 588 55 16
##BELGIUM
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

#+52 55 4161 3617
## MEXICO
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_treeMX/call_handler.php

#+1 615 645 1041
## INTERNATIONAL
Webhooks/TwiML
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

===========================================================================================
									FAIL HANDLERS
===========================================================================================
#Primary handler fails
>http://52.26.104.12/linguist/phone_tree/drop_vm.php
#CALL STATUS CHANGE
>http://alliantranslate.com/linguist/phone_tree/status_callback.php

===========================================================================================
								REGISTERED USER HOTLINE
===========================================================================================
# Registered User Hotline	1 855-733-6655
# US
>http://alliantranslate.com/linguist/phoneapp/regular/greetingregular.php

#Registered User Hotline	1 855-733-6655
# CA
>http://alliantranslate.com/linguist/phoneapp/regular/greetingregular.php

# Registered User Hotline	+44 800 802 1231
## PhoneApp Registered UK
>http://alliantranslate.com/linguist/phoneapp/regular/greetingregular.php

#Registered User Hotline	+61 3 8609 8382
## PhoneApp RegisterAU
>http://alliantranslate.com/linguist/phoneapp/regular/greetingregular.php

===========================================================================================
						PHONEAPP INTERPRETER ZA REGISTERED USER HOTLINE
===========================================================================================
# $outboundnum = "+16152099121";
## INTERPRETER ZOVE Registered User Hotline ili callrandom.php
>http://alliantranslate.com/linguist/phoneapp/interpreter.php tokens.php PhoneApp/

===========================================================================================
	             	DIAL SERVICE NUMBER alliantelephonic.com/content/pricing
===========================================================================================
#+18557732512
##Client PhoneApp US
>http://alliantranslate.com/linguist/phoneapp/greeting.php

#+18557732512
##Client PhoneApp CA
>http://alliantranslate.com/linguist/phoneapp/greeting.php

# +448008021269
##PhoneApp OneTime UK
>http://alliantranslate.com/linguist/phoneapp/greeting.php

# +61388996907
##PhoneApp OneTime AU
>http://alliantranslate.com/linguist/phoneapp/greeting.php

===========================================================================================
								TWILIO-CONFERENCE ENHANCED
===========================================================================================
# $client_dial = '+18555129043';
## KLIENT ZOVE
>http://alliantranslate.com/linguist/twilio-conf-enhanced/

# $inter_dial='+16156451009';
# INTERPRETER ZOVE
>http://alliantranslate.com/linguist/twilio-conf-enhanced/

===========================================================================================
							SMS FOR TWILIO-CONFERENCE-ENHANCED
===========================================================================================
# $client_dial = '+18555129043';
## SMS
>http://alliantranslate.com/linguist/twilio-conf-enhanced/sms.php

===========================================================================================
								NEVIDIM DA SE KORISTE
===========================================================================================
# +1 615-209-9060
>https://demo.twilio.com/welcome/voice/

# (408) 831-3645
## IDE NA
>http://alliantranslate.com/linguist/phoneapp/regular/greetingregular.php

# +16152570118
## TWIMLET IDE NA BROJ +16159263941
>http://twimlets.com/forward?PhoneNumber=%2B16159263941&Timeout=60&

# +16154920105
## Alen Test Number
>http://alliantranslate.com/linguist_dev_test_backup/phoneapp/greeting.php

# +18777512797
## twilio-conf-enhanced IDE NA
>http://alliantranslate.com/linguist/twilio-conf-enhanced/

# +18667997678
## phone_tree/call_handler.php IDE NA
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

#+17862323230
## phone_tree/call_handler.php IDE NA
>http://alliantranslate.com/linguist/phone_tree/call_handler.php

===========================================================================================