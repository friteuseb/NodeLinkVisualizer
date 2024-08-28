<?php
namespace TalanHdf\NodeLinkVisualizer\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NodeLinkVisualizerController extends ActionController
{
    private ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    public function showAction(): ResponseInterface
    {
        $displayMode = $this->settings['displayMode'] ?? 'basic';
        $parentPage = (int)($this->settings['parentPage'] ?? 0);
        $recursionLevel = (int)($this->settings['recursionLevel'] ?? 1);

        $nodes = $this->getNodes($parentPage, $recursionLevel);
        $links = $this->getLinks($parentPage, $recursionLevel);

        $analyzedLinks = $GLOBALS['TYPO3_CONF_VARS']['USER']['node_link_visualizer_links'] ?? [];

        $logFile = GeneralUtility::getFileAbsFileName('typo3temp/logs/node_link_visualizer.log');
        $logContent = file_exists($logFile) ? file_get_contents($logFile) : 'Aucun log trouvé';

        $this->view->assign('nodes', $nodes);
        $this->view->assign('links', $links);
        $this->view->assign('analyzedLinks', $analyzedLinks);
        $this->view->assign('displayMode', $displayMode);
        $this->view->assign('debug', 'Le contrôleur fonctionne ! ParentPage: ' . $parentPage . ', RecursionLevel: ' . $recursionLevel . ', Page courante: ' . $GLOBALS['TSFE']->id);
        $this->view->assign('logContent', $logContent);

        return $this->htmlResponse();
    }



    private function getPageContent(int $pageId): string
    {
        $page = $this->pageRepository->getPage($pageId);
        if ($page) {
            return $page['content'] ?? 'Pas de contenu trouvé pour cette page.';
        }
        return 'Page non trouvée.';
    }

    private function analyzePageContent(string $content): array
    {
        $links = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($content);
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            if (strpos($href, 'index.php?id=') !== false || strpos($href, '?id=') !== false) {
                preg_match('/id=(\d+)/', $href, $matches);
                if (isset($matches[1])) {
                    $links[] = [
                        'source' => $this->settings['parentPage'],
                        'target' => (int)$matches[1],
                        'type' => 'generated_link',
                        'href' => $href,
                        'text' => $anchor->textContent
                    ];
                }
            }
        }
        return $links;
    }

    private function getNodes(int $parentPage, int $recursionLevel): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $result = $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentPage, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAllAssociative();

        $nodes = array_map(function($page) {
            return [
                'id' => $page['uid'],
                'name' => $page['title']
            ];
        }, $result);

        if ($recursionLevel > 1) {
            foreach ($result as $page) {
                $nodes = array_merge($nodes, $this->getNodes($page['uid'], $recursionLevel - 1));
            }
        }

        return $nodes;
    }


    private function getLinks(int $parentPage, int $recursionLevel): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $result = $queryBuilder
            ->select('uid', 'pid', 'header', 'bodytext', 'list_type', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentPage, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAllAssociative();

        $links = [];
        foreach ($result as $content) {
            // Chercher les liens dans bodytext
            preg_match_all('/t3:\/\/page\?uid=(\d+)/', $content['bodytext'], $matches);
            foreach ($matches[1] as $targetUid) {
                $links[] = [
                    'source' => $content['pid'],
                    'target' => (int)$targetUid,
                    'type' => 'bodytext'
                ];
            }

            // Vérifier si c'est un élément de contenu de type "semantic suggestion"
            if ($content['list_type'] === 'semanticsuggestion_pi1') {
                $flexformData = GeneralUtility::xml2array($content['pi_flexform']);
                // Vous devrez ajuster cette partie en fonction de la structure réelle du flexform
                if (isset($flexformData['data']['sDEF']['lDEF']['settings.links']['vDEF'])) {
                    $suggestedLinks = explode(',', $flexformData['data']['sDEF']['lDEF']['settings.links']['vDEF']);
                    foreach ($suggestedLinks as $linkUid) {
                        $links[] = [
                            'source' => $content['pid'],
                            'target' => (int)$linkUid,
                            'type' => 'semantic_suggestion'
                        ];
                    }
                }
            }
        }

        if ($recursionLevel > 1) {
            $subpages = $this->getNodes($parentPage, 1);
            foreach ($subpages as $subpage) {
                $links = array_merge($links, $this->getLinks($subpage['id'], $recursionLevel - 1));
            }
        }

        return $links;
    }
    
    private function getRawContentData(int $parentPage): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        return $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentPage, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAllAssociative();
    }
    
}