<?php
class NewArticleTemplatesHooks {
	/**
	 * preload returns the text that is in the article specified by $preload
	 */
	public static function preload( $preload ) {
		if ( $preload === '' ) {
			return '';
		}

		$preloadTitle = Title::newFromText( $preload );
		if ( !$preloadTitle ) {
			return '';
		}
		
		$article = WikiPage::factory( $preloadTitle );
		if ( !$article ) {
			return '';
		}

		$text = ContentHandler::getContentText( $article->getContent() );
		// Remove <noinclude> sections and <includeonly> tags from text
		$text = StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $text );
		$text = strtr( $text, ['<includeonly>' => '', '</includeonly>' => ''] );
		return $text;
	}

	/**
	 * called by Hook EditPage::showEditForm:initial.
	 * Simply preloads the textbox with a text that is defined in an
	 * article. Also see preload function above.
	 */
	public static function onNewArticle( $newPage ) {
		global $wgNewArticleTemplatesNamespaces, $wgNewArticleTemplatesOnSubpages,
			$wgNewArticleTemplatesDefault, $wgNewArticleTemplatesPerNamespace;
		
		/* some checks */
		if ( $newPage->mTitle->exists() or $newPage->firsttime != 1 ) {
			return true;
		}

		/* see if this is a subpage */
		$title = $newPage->mTitle;
		$isSubpage = false;

		if ( $title->isSubpage() ) {
			$baseTitle = Title::newFromText(
				$title->getBaseText(),
				$title->getNamespace() );
			if ( $baseTitle->exists() ) {
				$isSubpage = true;
			}
		}

		/* we might want to return if this is a subpage */
		if ( (!$wgNewArticleTemplatesOnSubpages) && $isSubpage ) {
			return true;
		}

		$namespace = $title->getNamespace();

		if (!array_key_exists($namespace, $wgNewArticleTemplatesNamespaces)) {
			return true;
		}
		
		/* actually important code: */
		if ( $wgNewArticleTemplatesPerNamespace[$namespace] ) {
			$template = $wgNewArticleTemplatesPerNamespace[$namespace];
		}
		elseif ( $wgNewArticleTemplatesDefault ) {
			$template = $wgNewArticleTemplatesDefault;
		}

		/* if this is a subpage, we want to to use $template/Subpage instead, if it exists */
		if ( $isSubpage && Title::newFromText( $template . '/Subpage' )->exists() ) {
			$template = $template . '/Subpage';
		}
		$newPage->textbox1 = self::preload( $template );
		return true;
	}

}
