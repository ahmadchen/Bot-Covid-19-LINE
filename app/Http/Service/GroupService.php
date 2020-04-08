<?php

namespace App\Http\Service;

use App\Repositories\GroupRepository;
use LINE\LINEBot;

class GroupService
{
    private $helpService;
    private $groupRepository;

    public function __construct(
        HelpService $helpService,
        GroupRepository $groupRepository
    )
    {
        $this->helpService =  $helpService;
        $this->groupRepository = $groupRepository;
    }

    public function joinGroup(LINEBot $bot, $event)
    {
        if ($event['source']['type'] == 'group')
        {
            $groupID = $event['source']['groupId'];
            $this->groupRepository->insert($groupID);

            $this->helpService->sendGreetings($bot, $event);
        }
    }

    public function leftGroup(LINEBot $bot, $event)
    {
        if ($event['source']['type'] == 'group')
        {
            $groupID = $event['source']['groupId'];
            $this->groupRepository->delete($groupID);
        }
    }
}
