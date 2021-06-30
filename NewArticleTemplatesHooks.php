<?php
class NewArticleTemplatesHooks {
	/**
	 * preload returns the text that is in the article specified by $preload
	 */
	public function preload( $preload ) {
		if ( $preload === '' )
			return '';
		else {
			// based on EditPage::getPreloadedText(), present until 1.23.x
			$preloadTitle = Title::newFromText( $preload );
			// if ( isset( $preloadTitle ) && $preloadTitle->userCan('read') ) {
			if ( isset( $preloadTitle ) ) {
				$rev=Revision::newFromTitle($preloadTitle);
				if ( is_object( $rev ) ) {
					$text = ContentHandler::getContentText( $rev->getContent( Revision::RAW ) );
					// Remove <noinclude> sections and <includeonly> tags from text
					$text = StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $text );
					$text = strtr( $text, ['<includeonly>' => '', '</includeonly>' => ''] );
					return $text;
				} else
					return '';
			}
		}
	}

	/**
	 * called by Hook EditPage::showEditForm:initial.
	 * Simply preloads the textbox with a text that is defined in an
	 * article. Also see preload function above.
	 */
	public function onNewArticle( $newPage ) {
		global $wgNewArticleTemplatesNamespaces, $wgNewArticleTemplatesOnSubpages,
			$wgNewArticleTemplatesDefault, $wgNewArticleTemplatesPerNamespace;
		
		/* some checks */
		if ( $newPage->mTitle->exists() or $newPage->firsttime != 1 )
			return true;

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
		if ( (! $wgNewArticleTemplatesOnSubpages) && $isSubpage )
			return true;

		$namespace = $title->getNamespace();

		/* actually important code: */
		if (array_key_exists($namespace, $wgNewArticleTemplatesNamespaces))
		{
			if ( $wgNewArticleTemplatesPerNamespace[$namespace] )
				$template = $wgNewArticleTemplatesPerNamespace[$namespace];
			elseif ( $wgNewArticleTemplatesDefault )
				$template = $wgNewArticleTemplatesDefault;

			/* if this is a subpage, we want to to use $template/Subpage instead, if it exists */
			if ( $isSubpage ) {
				$subpageTemplate = Title::newFromText( $template . '/Subpage' );
				if ( $subpageTemplate->exists() ) {
					$template = $template . '/Subpage';
				}
			}
			$newPage->textbox1 = self::preload( $template );
		}
		return true;
	}

}
