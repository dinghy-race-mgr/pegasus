<?php
/*
 * dtm_confirm_check
 *
 * Design
 *  - cronlog start of process
 *  - check confirm status for each duty in t_ebventduty
 *  - if not confirmed add to array of members to receive reminder
 *  -  check no. of reminders sent to duty person - if less than 3 then send initial reminder
 *     if 3 or more send final reminder
 *  - construct json payload for reminder emails
 *  - send emails using brevo
 *  - if reminders sent then update reminder count in t_eventduty
 *  - update cronlog with no. of each type of reminder sent
 *  - cronlog end of process
 *
 */
