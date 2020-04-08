<?php

namespace App\Http\Service;

use App\Traits\MessageHelper;
use Illuminate\Support\Facades\Log;
use Line\LINEBot;

use LINE\LINEBot\Constant\Flex\ComponentButtonHeight;
use LINE\LINEBot\Constant\Flex\ComponentButtonStyle;
use LINE\LINEBot\Constant\Flex\ComponentFontSize;
use LINE\LINEBot\Constant\Flex\ComponentFontWeight;
use LINE\LINEBot\Constant\Flex\ComponentIconSize;
use LINE\LINEBot\Constant\Flex\ComponentImageAspectMode;
use LINE\LINEBot\Constant\Flex\ComponentImageAspectRatio;
use LINE\LINEBot\Constant\Flex\ComponentImageSize;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\Constant\Flex\ComponentPosition;
use LINE\LINEBot\Constant\Flex\ComponentMargin;
use LINE\LINEBot\Constant\Flex\ComponentSpaceSize;
use LINE\LINEBot\Constant\Flex\ComponentSpacing;
use LINE\LINEBot\Constant\Flex\BubleContainerSize;
use LINE\LINEBot\Constant\Flex\ComponentTextDecoration;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\IconComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SpacerComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SpanComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;

class HelpService
{
    use MessageHelper;

    public function sendGreetings (LINEBot $bot, $event)
    {
        $components = [];

        $descriptions = "Halo, saya adalah Bot Info Covid-19\n".
        "Saya di sini bertugas untuk memberikan informasi mengenai Covid-19.\n".
        "Teman-teman dapat menginputkan perintah berikut:\n";

        $descriptionComponent = TextComponentBuilder::builder()
        ->setText($descriptions)
        ->setSize(ComponentFontSize::XS)
        ->setWrap(true);
        $components[] = $descriptionComponent;

        $components = array_merge($components, $this->getCommandHelperComponents());

        $flex = FlexMessageBuilder::builder()
                ->setAltText("Greetings")
                ->setContents(
                    BubbleContainerBuilder::builder()
                    ->setHeader($this->getHeaderComponent('Bot Covid-19'))
                    ->setBody(
                        BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setBackgroundColor('#fafafa')
                        ->setContents($components)
                    )
                    ->setFooter($this->getFooterComponent())
                    ->setSize(BubleContainerSize::MEGA)
                );

        $bot->replyMessage($event['replyToken'], $flex);
    }

    public function sendHelp(LINEBot $bot, $event)
    {
        $components = [];
        $components = array_merge($components, $this->getCommandHelperComponents());

        $flex = FlexMessageBuilder::builder()
                ->setAltText("Help")
                ->setContents(
                    BubbleContainerBuilder::builder()
                    ->setHeader($this->getHeaderComponent('Daftar Perintah Bot Covid-19'))
                    ->setBody(
                        BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setBackgroundColor('#fafafa')
                        ->setContents($this->getCommandHelperComponents())
                    )
                    ->setFooter($this->getFooterComponent())
                    ->setSize(BubleContainerSize::MEGA)
                );

        return $bot->replyMessage($event['replyToken'], $flex);
    }

    public function getHeaderComponent($title)
    {
        $textComponent = TextComponentBuilder::builder()
        ->setText($title)
        ->setWeight(ComponentFontWeight::BOLD)
        ->setSize(ComponentFontSize::MD)
        ->setWrap(true)
        ->setColor("#FFFFFF");

        $boxComponent = BoxComponentBuilder::builder()
        ->setLayout(ComponentLayout::VERTICAL)
        ->setBackgroundColor('#3498db')
        ->setPaddingAll('8%')
        ->setContents([$textComponent]);

        return $boxComponent;
    }

    private function getCommandHelperComponents()
    {
        $components = [];
        $commands = [
            ["/covid info global", "Untuk mengetahui statistik global"],
            ["/covid info <negara>", "Untuk mengetahui statistik negara"],
            ["/covid help", "Untuk melihat daftar perintah"],
            ["/covid provinsi", "Untuk mengetahui statistik semua provinsi"],
            ["/covid provinsi <nama provinsi>", "Untuk mengetahui statistik di provinsi tertentu"]
        ];

        foreach ($commands as $command)
        {
            $spacerComponent = SpacerComponentBuilder::builder();

            $bulletComponent = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setBackgroundColor('#fafafa')
            ->setContents([$spacerComponent]);

            $bulletVerticalBoxComponent = BoxComponentBuilder::builder()
            ->setBorderColor("#e74c3c")
            ->setOffsetTop("3px")
            ->setWidth("12px")
            ->setHeight("12px")
            ->setBorderWidth("2px")
            ->setCornerRadius("30px")
            ->setLayout(ComponentLayout::VERTICAL)
            ->setBackgroundColor('#fafafa')
            ->setContents([$bulletComponent]);

            $commandTitleComponent = TextComponentBuilder::builder()
            ->setOffsetStart("5px")
            ->setText($command[0])
            ->setWeight(ComponentFontWeight::BOLD)
            ->setSize(ComponentFontSize::XS)
            ->setWrap(true);

            $commandDescComponent = TextComponentBuilder::builder()
            ->setText($command[1] . "\n")
            ->setSize(ComponentFontSize::XS)
            ->setWrap(true);

            $commandTitleBoxComponent = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::HORIZONTAL)
            ->setContents([$bulletVerticalBoxComponent, $commandTitleComponent]);

            $commandBoxComponent = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setBackgroundColor('#fafafa')
            ->setContents([$commandTitleBoxComponent, $commandDescComponent]);

            $components[] = $commandBoxComponent;
        }

        return $components;
    }

    public function getFooterComponent()
    {
        $madeByLoveComponent = TextComponentBuilder::builder()
        ->setText("Made with love")
        ->setWeight(ComponentFontWeight::BOLD)
        ->setSize(ComponentFontSize::XS)
        ->setWrap(true);

        $tagComponent = TextComponentBuilder::builder()
        ->setText("#PhysicalDistancing #StaySafe")
        ->setWeight(ComponentFontWeight::BOLD)
        ->setColor("#2980b9")
        ->setSize(ComponentFontSize::XS)
        ->setWrap(true);

        $contactComponent = TextComponentBuilder::builder()
        ->setText("- cacadosman23 (LINE & FB)\n- fadli.m@mail.ugm.ac.id (Email)")
        ->setSize(ComponentFontSize::XS)
        ->setWrap(true);

        $footerComponent = BoxComponentBuilder::builder()
        ->setLayout(ComponentLayout::VERTICAL)
        ->setBackgroundColor('#fafafa')
        ->setContents([$madeByLoveComponent, $tagComponent, $contactComponent]);

        return $footerComponent;
    }
}
