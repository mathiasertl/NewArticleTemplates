<?php

$wgHooks['EditPage::showEditForm:initial'][] = 'newArticleTemplates';

/**
 * preload returns the text that is in the article specified by $preload
 */
function preload( $preload ) {
	if ( $preload === '' )
		return '';
	else {
		$preloadTitle = Title::newFromText( $preload );
		if ( isset( $preloadTitle ) && $preloadTitle->userCanRead() ) {
			$rev=Revision::newFromTitle($preloadTitle);
			if ( is_object( $rev ) ) {
				$text = $rev->getText();
				// TODO FIXME: AAAAAAAAAAA, this shouldn't be implementing
				// its own mini-parser! -ævar
				$text = preg_replace( '~</?includeonly>~', '', $text );
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
function newArticleTemplates( $newPage ) {
	global $wgNewArticleTemplatesEnable;

	/* some checks */
	if ( $newPage->mTitle->exists() or $newPage->firsttime != 1 or !$wgNewArticleTemplatesEnable )
		return true;

	global $wgNewArticleTemplatesNamespaces, $wgNewArticleTemlatesOnSubpages;

	/* we might want to return if this is a subpage */
	if ( (!$wgNewArticleTemplatesOnSubpages) and $newPage->mTitle->isSubpage() )
		return true;

	$namespace = $newPage->mTitle->getNamespace();

	/* actually important code: */
	if ( $wgNewArticleTemplatesNamespaces[$namespace] )
	{
		global $wgNewArticleTemplatesDefault, $wgNewArticleTemplates_PerNamespace;

		if ( $wgNewArticleTemplates_PerNamespace[$namespace] )
			$newPage->textbox1 = preload($wgNewArticleTemplates_PerNamespace[$namespace]);
		elseif ( $wgNewArticleTemplatesDefault )
			$newPage->textbox1 = preload($wgNewArticleTemplatesDefault);
	}
	return true;
}

?>
