<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;

$wgHooks['EditPage::showEditForm:initial'][] = 'newArticleTemplates';

$wgExtensionCredits['other'][] = array (
	'name' => 'NewArticleTemplate',
	'description' => 'Prefills new articles with a given article',
	'version' => '1.2-1.36.2',
	'author' => 'Mathias Ertl, Fabian Zeindl, someone-somenet-org',
	'url' => 'http://www.mediawiki.org/wiki/Extension:NewArticleTemplates',
);

/**
 * preload returns the text that is in the article specified by $preload
 */
function preload( $preload ) {
	if ( $preload === '' )
		return '';
	else {
		$preloadTitle = Title::newFromText( $preload );
		if ( isset( $preloadTitle ) && MediaWikiServices::getInstance()->getPermissionManager()->userCan( 'read', RequestContext::getMain()->getUser(), $preloadTitle) ) {
			$rev=Revision::newFromTitle($preloadTitle);
			if ( is_object( $rev ) ) {
				$content = $rev->getContent( Revision::RAW );
				$text = ContentHandler::getContentText( $content );
				$text = StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $text );
				$text = strtr( $text, array( '<includeonly>' => '', '</includeonly>' => '' ) );
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

	global $wgNewArticleTemplatesNamespaces, $wgNewArticleTemplatesOnSubpages;

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
		global $wgNewArticleTemplatesDefault, $wgNewArticleTemplates_PerNamespace;

		if ( $wgNewArticleTemplates_PerNamespace[$namespace] )
			$template = $wgNewArticleTemplates_PerNamespace[$namespace];
		elseif ( $wgNewArticleTemplatesDefault )
			$template = $wgNewArticleTemplatesDefault;

		/* if this is a subpage, we want to to use $template/Subpage instead, if it exists */
		if ( $isSubpage ) {
			$subpageTemplate = Title::newFromText( $template . '/Subpage' );
			if ( $subpageTemplate->exists() ) {
				$template = $template . '/Subpage';
			}
		}

		$newPage->textbox1 = preload( $template );
#			$newPage->textbox1 = preload($wgNewArticleTemplatesDefault);
	}
	return true;
}
