<?php

require __DIR__ . "/vendor/autoload.php";

const TEAM_AMOUNT      = 3;
const TEAM_SIZE        = 4;
const AMOUNT_OF_MEMBER = TEAM_AMOUNT * TEAM_SIZE;


$teamPreference = new App\TeamPreferences(__DIR__);
// To generate a random set of choice
if (!file_exists(__DIR__ . "/teamPreferences.yaml")) {
    $teamPreference->generateRandomSetting(AMOUNT_OF_MEMBER, TEAM_SIZE);
}


$linkManager   = new App\LinkManager;
$memberManager = new App\MemberManager($linkManager);

// We first register every member
foreach ($teamPreference->getMembers() as $member) {
    $memberManager->addMember($member);
}

// Then for each member we weight the link
foreach ($teamPreference->getMembers() as $member) {
    $memberManager->addPreferences(
        $member,
        $teamPreference->getPreferences($member)
    );
}

// To output a plantuml diagram to check the links
$teamPreference->outputPlantUml(__DIR__ . "/teamPreferences.puml");
$linkManager->outputPlantUml(__DIR__ . "/weightedRelation.puml", true);
$linkManager->outputPlantUml(__DIR__ . "/weightedRelation-all.puml", false);

// We create a pool of team
$teamManager = new \App\TeamManager(TEAM_AMOUNT, TEAM_SIZE);

// And we populate them following preferences
foreach ($linkManager->getOrderedLinkWithPositiveWeight() as $link) {
    $teamManager->digestLink($link);
}

// At the end, we add all the member that didn't had a team to a random team
foreach ($teamPreference->getMembers() as $member) {
    $teamManager->ensureMemberAsTeam($member);
}


$teamManager->outputResult();


$amountOfPreferences            = 0;
$amountOfPreferencesSuccessfull = 0;
foreach ($teamPreference->getMembers() as $member) {
    $memberTeam = $teamManager->getMemberTeam($member);
    printf("== %s ==\n",
        $member
    );

    foreach ($teamPreference->getPreferences($member) as $otherMember) {
        $successfull = $memberTeam->containMember($otherMember);
        $amountOfPreferences++;
        if ($successfull) {
            $amountOfPreferencesSuccessfull++;
        }
        printf("- Wanted to be with : %s => %s\n",
            $otherMember,
            $successfull ? "Ok" : "Not possible"
        );
    }
    printf("\n");

}
printf("Success %% : %d\n\n",
    round($amountOfPreferencesSuccessfull / $amountOfPreferences * 100)
);
