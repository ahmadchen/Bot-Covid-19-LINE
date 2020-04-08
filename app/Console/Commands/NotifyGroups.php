<?php

namespace App\Console\Commands;

use App\Repositories\CovidCacheRepository;
use App\Http\Service\HelpService;
use App\Http\Service\IndoCovidHttpService;
use App\Repositories\GroupRepository;
use App\Traits\MessageHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Constant\Flex\BubleContainerSize;
use LINE\LINEBot\Constant\Flex\ComponentFontSize;
use LINE\LINEBot\Constant\Flex\ComponentFontWeight;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;

class NotifyGroups extends Command
{
    use MessageHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifikasi Total Kasus Covid-19 di Indonesia';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(
        IndoCovidHttpService $indoCovidHttpService,
        HelpService $helpService,
        CovidCacheRepository $covidCacheRepository,
        GroupRepository $groupRepository
    )
    {
        Log::debug('Notification executed');

        $channelSecret = env("CHANNEL_SECRET");
        $channelToken  = env("CHANNEL_TOKEN");
        $bot = new LINEBot(new CurlHTTPClient($channelToken), [
            'channelSecret' => $channelSecret
        ]);

        $data = $indoCovidHttpService->getIndonesiaData();
        $cache = $covidCacheRepository->get("total_cases_indo");

        if ($cache == null || $data['total'] < intval($cache->value))
        {
            $covidCacheRepository->set('total_cases_indo', $data['total']);
            $groups = $groupRepository->all();

            $components = [];
            $components[] = TextComponentBuilder::builder()
            ->setSize(ComponentFontSize::SM)
            ->setText('Indonesia hari ini')
            ->setWeight(ComponentFontWeight::BOLD)
            ->setColor("#e74c3c");

            $components[] = TextComponentBuilder::builder()
            ->setText(" ");

            $components[] = TextComponentBuilder::builder()
            ->setSize(ComponentFontSize::XS)
            ->setText("Total Kasus: " . $this->formatNum($data['total']));

            $components[] = TextComponentBuilder::builder()
            ->setSize(ComponentFontSize::XS)
            ->setText("Sembuh: " . $this->formatNum($data['recovered']));

            $components[] = TextComponentBuilder::builder()
            ->setSize(ComponentFontSize::XS)
            ->setText("Meninggal: " .$this->formatNum($data['deaths']));


            $flex = FlexMessageBuilder::builder()
                    ->setAltText("Update Otomatis Kasus Covid-19 di Indonesia")
                    ->setContents(
                        BubbleContainerBuilder::builder()
                        ->setHeader($helpService->getHeaderComponent("Update Otomatis Kasus Covid-19 di Indonesia"))
                        ->setBody(
                            BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::VERTICAL)
                            ->setBackgroundColor('#fafafa')
                            ->setContents($components)
                        )
                        ->setFooter($helpService->getFooterComponent())
                        ->setSize(BubleContainerSize::MEGA)
                    );

            foreach ($groups as $group)
            {
                try {
                    $id = $group->group_id;
                    $bot->pushMessage($id, $flex);
                } catch (\Exception $e) {

                }
            }
        }
    }
}
