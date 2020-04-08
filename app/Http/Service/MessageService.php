<?php

namespace App\Http\Service;

use App\Traits\MessageHelper;
use Line\LINEBot;
use App\Http\Service\HelpService;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Constant\Flex\BubleContainerSize;
use LINE\LINEBot\Constant\Flex\ComponentFontSize;
use LINE\LINEBot\Constant\Flex\ComponentFontWeight;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;

class MessageService
{
    use MessageHelper;

    private $helpService;
    private $globalCovidHttpService;
    private $indoCovidHttpService;

    public function __construct(
        HelpService $helpService,
        GlobalCovidHttpService $globalCovidHttpService,
        IndoCovidHttpService $indoCovidHttpService
    )
    {
        $this->helpService = $helpService;
        $this->globalCovidHttpService = $globalCovidHttpService;
        $this->indoCovidHttpService = $indoCovidHttpService;
    }

    public function handleMessages(LINEBot $bot, $event)
    {
        if ($event['source']['type'] == 'user')
        {
            $this->sendOnlyForGroupWarning($bot, $event);
            return;
        }

        if ($event['message']['type'] != 'text')
        {
            return;
        }

        $message = $event['message']['text'];
        if ($this->isCommand($message))
        {
            if ($event['source']['type'] == 'group')
                $this->passCommand($bot, $event, $this->getParam($message, 1));
            else
                $this->sendOnlyForGroupWarning($bot, $event);
        }
    }

    private function sendOnlyForGroupWarning(LINEBot $bot, $event)
    {
        $bot->replyText($event['replyToken'], "Mohon maaf, bot ini hanya dapat digunakan di group chat.");
    }

    private function passCommand(LINEBot $bot, $event, $command)
    {
        switch($command)
        {
            case "info":
                $this->commandInfoHandler($bot, $event);
                break;
            case "subscribe":
                break;
            case "unsubscribe":
                break;
            case "provinsi":
                $this->commandProvinceHandler($bot, $event);
                break;
            case "pencegahan":
                break;
            case "prediksi":
                break;
            default:
                $this->helpService->sendHelp($bot, $event);
        }
    }

    private function commandInfoHandler(LINEBot $bot, $event)
    {
        $param = $this->sliceParamUntilEnd($event['message']['text'], 2);
        $param = strtolower($param);

        if ($param == 'global')
        {
            $this->sendCovidGlobalData($bot, $event);
            return;
        }

        $this->sendCovidCountryData($bot, $event, $param);
    }

    private function commandProvinceHandler(LINEBot $bot, $event)
    {
        if ($this->getParamCount($event['message']['text']) == 2)
        {
            $this->sendAllProvinceData($bot, $event);
            return;
        }

        $param = $this->sliceParamUntilEnd($event['message']['text'], 2);
        $param = strtolower($param);

        $this->sendProvinceData($bot, $event, $param);
    }

    private function sendCovidGlobalData(LINEBot $bot, $event)
    {
        $data = $this->globalCovidHttpService->getCovidGlobalData();

        $components = [];
        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Terkonfirmasi: " . $this->formatNum($data['confirmed']));

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Sembuh: " . $this->formatNum($data['recovered']));

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Meninggal: " .$this->formatNum($data['deaths']));


        $flex = FlexMessageBuilder::builder()
                ->setAltText("Info Covid-19 Global")
                ->setContents(
                    BubbleContainerBuilder::builder()
                    ->setHeader($this->helpService->getHeaderComponent('Info Covid-19 Global'))
                    ->setBody(
                        BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setBackgroundColor('#fafafa')
                        ->setContents($components)
                    )
                    ->setFooter($this->helpService->getFooterComponent())
                    ->setSize(BubleContainerSize::MEGA)
                );

        $bot->replyMessage($event['replyToken'], $flex);
    }

    private function sendCovidCountryData(LINEBot $bot, $event, $param)
    {
        $data = $this->globalCovidHttpService->getCovidCountryData($param);
        if ($data == null)
        {
            $bot->replyText($event['replyToken'], "Nama negara tidak ditemukan.");
            return;
        }

        $components = [];
        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::SM)
        ->setText($data['country'])
        ->setWeight(ComponentFontWeight::BOLD)
        ->setColor("#e74c3c");

        $components[] = TextComponentBuilder::builder()
        ->setText(" ");

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Terkonfirmasi: " . $this->formatNum($data['confirmed']));

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Sembuh: " . $this->formatNum($data['recovered']));

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Meninggal: " .$this->formatNum($data['deaths']));


        $flex = FlexMessageBuilder::builder()
                ->setAltText("Info Covid-19 di " . $data['country'])
                ->setContents(
                    BubbleContainerBuilder::builder()
                    ->setHeader($this->helpService->getHeaderComponent("Info Covid-19 di " . $data['country']))
                    ->setBody(
                        BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setBackgroundColor('#fafafa')
                        ->setContents($components)
                    )
                    ->setFooter($this->helpService->getFooterComponent())
                    ->setSize(BubleContainerSize::MEGA)
                );

        $bot->replyMessage($event['replyToken'], $flex);
    }

    private function sendAllProvinceData(LINEBot $bot, $event)
    {
        $provinces = $this->indoCovidHttpService->getAllProvinceData();

        $components = [];

        foreach ($provinces as $data)
        {
            $components[] = TextComponentBuilder::builder()
            ->setWeight(ComponentFontWeight::BOLD)
            ->setColor("#e74c3c")
            ->setSize(ComponentFontSize::XS)
            ->setText($data['name']);

            $components[] = TextComponentBuilder::builder()
            ->setSize(ComponentFontSize::XS)
            ->setText("Positif: " . $this->formatNum($data['positive']));

            $components[] = TextComponentBuilder::builder()
            ->setSize(ComponentFontSize::XS)
            ->setText("Sembuh: " . $this->formatNum($data['recovered']));

            $components[] = TextComponentBuilder::builder()
            ->setSize(ComponentFontSize::XS)
            ->setText("Meninggal: " .$this->formatNum($data['deaths']));

            $components[] = TextComponentBuilder::builder()
            ->setText(" ");
        }
        array_pop($components);

        $flex = FlexMessageBuilder::builder()
                ->setAltText("Info Covid-19 di Semua Provinsi")
                ->setContents(
                    BubbleContainerBuilder::builder()
                    ->setHeader($this->helpService->getHeaderComponent('Info Covid-19 di Semua Provinsi'))
                    ->setBody(
                        BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setBackgroundColor('#fafafa')
                        ->setContents($components)
                    )
                    ->setFooter($this->helpService->getFooterComponent())
                    ->setSize(BubleContainerSize::MEGA)
                );

        $bot->replyMessage($event['replyToken'], $flex);
    }

    private function sendProvinceData(LINEBot $bot, $event, $param)
    {
        $data = $this->indoCovidHttpService->getProvinceData($param);
        if ($data == null)
        {
            $bot->replyText($event['replyToken'], "Nama provinsi tidak ditemukan.");
            return;
        }

        $components = [];
        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::SM)
        ->setText($data['name'])
        ->setWeight(ComponentFontWeight::BOLD)
        ->setColor("#e74c3c");

        $components[] = TextComponentBuilder::builder()
        ->setText(" ");

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Positif: " . $this->formatNum($data['positive']));

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Sembuh: " . $this->formatNum($data['recovered']));

        $components[] = TextComponentBuilder::builder()
        ->setSize(ComponentFontSize::XS)
        ->setText("Meninggal: " .$this->formatNum($data['deaths']));


        $flex = FlexMessageBuilder::builder()
                ->setAltText("Info Covid-19 di Provinsi " . $data['name'])
                ->setContents(
                    BubbleContainerBuilder::builder()
                    ->setHeader($this->helpService->getHeaderComponent("Info Covid-19 di Provinsi " . $data['name']))
                    ->setBody(
                        BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setBackgroundColor('#fafafa')
                        ->setContents($components)
                    )
                    ->setFooter($this->helpService->getFooterComponent())
                    ->setSize(BubleContainerSize::MEGA)
                );

        $bot->replyMessage($event['replyToken'], $flex);
    }
}
