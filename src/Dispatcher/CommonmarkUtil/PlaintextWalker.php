<?php

declare(strict_types=1);

namespace Markdom\Dispatcher\CommonmarkUtil;

use ChromaX\StringBuilder\StringBuilder;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Newline;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;

/**
 * Class PlaintextWalker
 *
 * @package Markdom\Dispatcher\CommonmarkUtil
 */
final class PlaintextWalker
{

	/**
	 * @var StringBuilder
	 */
	private $plaintextBuilder;

	/**
	 * @param Node $node
	 * @return $this
	 */
	public function processNode(Node $node)
	{
		$this->plaintextBuilder = new StringBuilder();
		$walker = $node->walker();
		while ($event = $walker->next()) {
			$currentNode = $event->getNode();
			switch (get_class($currentNode)) {
				case DocumentProcessor::INLINE_NODE_CODE:
					/* @var Code $currentNode */
					$this
						->appendSpace()
						->appendPlaintext($currentNode->getContent());
					break;
				case DocumentProcessor::INLINE_NODE_NEWLINE:
					/* @var Newline $currentNode */
					$hard = $currentNode->getType() === Newline::HARDBREAK;
					if ($hard) {
						$this->appendSpace();
					}
					break;
				case DocumentProcessor::INLINE_NODE_TEXT:
					/* @var Text $currentNode */
					$this
						->appendSpace()
						->appendPlaintext($currentNode->getContent());
					break;
			}
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPlaintext(): ?string
	{
		return $this->plaintextBuilder->build();
	}

	/**
	 * @param string $plaintext
	 * @return $this
	 */
	private function appendPlaintext(string $plaintext)
	{
		$this->plaintextBuilder->append($plaintext);
		return $this;
	}

	/**
	 * @return $this
	 */
	private function appendSpace()
	{
		if ($this->plaintextBuilder->size() > 0 && $this->plaintextBuilder->lastChar() !== ' ') {
			$this->plaintextBuilder->append(' ');
		}
		return $this;
	}

}
