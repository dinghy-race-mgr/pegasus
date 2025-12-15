<?php

function validate_entries()
{
    /*
     *  Runs following checks
     *    1 - juniors on board
     *    2 - missing consent information
     *    3 - missing emergency contact
     *    4 - missing crew name for double hander
     *    5 - missing gender info for helm or crew
     *    6 - missing sail no.
     *    7 - how many junior consents still required
     *    rm_class - class not known to raceManager
     *    rm_comp - competitor not known to racemanager
     *
     */
    global $db_o, $entries;

    foreach ($entries as $k=>$entry)
    {
        //echo "<pre>".print_r($entry,true)."</pre>";

        $entry['crewnum'] > 1 ?  $doublehander = true : $doublehander = false;

        // check 1
        $entries[$k]['chk1'] = false;
        $num_juniors = 0;
        if (!empty($entry['h-age']) and $entry['h-age'] < 18) { $num_juniors++;}
        if (!empty($entry['c-age']) and $entry['c-age'] < 18) { $num_juniors++;}
        if ($num_juniors > 0)
        {
            $entries[$k]['chk1'] = $num_juniors." juniors";
        }

        // check 2
        $entries[$k]['chk2'] = false;
        if ($num_juniors > 0)  // check if we have consents
        {
            $num_consents = $db_o->run("SELECT count(*) as consents FROM e_consent WHERE entryid = ? 
                                        GROUP BY entryid", array($entry['id']) )->fetchColumn();
            if ($num_consents < $num_juniors) { $entries[$k]['chk2'] = "missing consents"; };
        }

        // check 3
        $entries[$k]['chk3'] = false;
        if (empty($entry['h-emergency']) and empty($entry['c-emergency'])) {$entries[$k]['chk3'] = "missing emergency contact";}

        // check 4
        $entries[$k]['chk4'] = false;
        if ($doublehander and (empty($entry['c-name']) or strtolower($entry['c-name'] == "tbc"
                    or strtolower($entry['c-name']) == "tba" or strtolower($entry['c-name']) == "tbd"))) {$entries[$k]['chk4'] = "missing crew name";}

        // check 5
        $entries[$k]['chk5'] = false;
        if ($entry['h-gender'] == 'not given' or ($doublehander and $entry['c-gender'] == 'not given')) {$entries[$k]['chk5'] = "missing gender";}

        // check 6
        $entries[$k]['chk6'] = false;
        if (empty($entry['b-sailno']) or strtolower($entry['b-sailno']) == "tbc"
            or strtolower($entry['b-sailno']) == "tba" or strtolower($entry['b-sailno']) == "tbd") {$entries[$k]['chk6'] = "missing sail number";}

        // check 7
        $entries[$k]['chk7'] = false;
        $num_consents_reqd = 0;
        if (!empty($entry['h-age']) and $entry['h-age'] < 18) { $num_consents_reqd++; }
        if (!empty($entry['c-age']) and $entry['c-age'] < 18) { $num_consents_reqd++; }
        $num_consents = $db_o->run("SELECT count(*) as consents FROM e_consent WHERE entryid = ? GROUP BY entryid", array($entry['id']) )->fetchColumn();
        $num_consents < $num_consents_reqd ? $entries[$k]['chk7'] = $num_consents_reqd - $num_consents." consents still reqd" : $entries[$k]['chk7'] = "";

        // check rm_class    [Is class known to raceManager]
        $entries[$k]['rm_class'] = false;
        if (empty($entry['b-pn'])) {$entries[$k]['rm_class'] = true;}

        // check rm_comp     [Does competitor record exist in raceManager]
        $entries[$k]['rm_comp'] = false;
        if (empty($entry['e-racemanager'])) {$entries[$k]['rm_comp'] = true;}

        // check rm_club     [Does club name match in raceManager competitor record]
        $entries[$k]['rm_club'] = false;
        if (empty($entry['e-racemanager']))
        {
            $entries[$k]['rm_club'] = true;
        }
        else
        {
            $comp = $db_o->run("SELECT * FROM t_competitor WHERE id = ?", array($entry['e-racemanager']) )->fetch();
            if ($entry['h-club'] != $comp['club']) { $entries[$k]['rm_club'] = true; }
        }

    }

    return $entries;
}