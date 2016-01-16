<?php

class TalkCommentEmailService extends EmailBaseService
{
    /**
     * @var TalkModel
     */
    protected $talk;

    protected $comment;

    /**
     * @var Request
     */
    private $request;

    public function __construct($config, $recipients, Request $request, TalkModel $talk, $comment)
    {
        // set up the common stuff first
        parent::__construct($config, $recipients);

        // this email needs talk and comment info
        $this->talk    = $talk;
        $this->comment = $comment['comments'][0];
        $this->request = $request;
    }

    public function sendEmail()
    {
        $this->setSubject("New feedback on " . $this->talk->talk_title);

        $byLine = '';

        if (isset($this->comment['user_display_name'])) {
            $byLine = ' by ' . $this->comment['user_display_name'];
        }

        if (empty($byLine) && isset($this->comment['username'])) {
            $byLine = ' by' . $this->comment['username'];
        }

        $outputView = $this->talk->getOutputView($this->request);

        $replacements = array(
            "title"   => $this->talk->talk_title,
            "rating"  => $this->comment['rating'],
            "comment" => $this->comment['comment'],
            "url"     => $outputView['website_uri'],
            "byline"  => $byLine
        );

        $messageBody = $this->parseEmail("commentTalk.md", $replacements);
        $messageHTML = $this->markdownToHtml($messageBody);

        $this->setBody($this->htmlToPlainText($messageHTML));
        $this->setHtmlBody($messageHTML);

        $this->dispatchEmail();
    }
}
