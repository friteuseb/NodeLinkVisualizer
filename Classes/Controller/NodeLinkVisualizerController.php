<?php
namespace TalanHdf\NodeLinkVisualizer\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class NodeLinkVisualizerController extends ActionController
{
    public function showAction(): ResponseInterface
    {
        $displayMode = $this->settings['displayMode'] ?? 'basic';
        $parentPage = (int)($this->settings['parentPage'] ?? 0);
        $recursionLevel = (int)($this->settings['recursionLevel'] ?? 1);

        $nodes = $this->getNodes($parentPage, $recursionLevel);
        $links = $this->getLinks($parentPage, $recursionLevel);

        $this->view->assign('nodes', $nodes);
        $this->view->assign('links', $links);
        $this->view->assign('displayMode', $displayMode);
        $this->view->assign('debug', 'Le contrôleur fonctionne ! ParentPage: ' . $parentPage . ', RecursionLevel: ' . $recursionLevel);
        $this->view->assign('settings', $this->settings);

        return $this->htmlResponse();
    }

    private function getNodes(int $parentPage, int $recursionLevel): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $result = $queryBuilder
            ->select('pid', 'header', 'bodytext')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentPage, \PDO::PARAM_INT)),
                $queryBuilder->expr()->like('bodytext', $queryBuilder->createNamedParameter('%t3://page?uid=%'))
            )
            ->execute()
            ->fetchAllAssociative();

        $links = [];
        foreach ($result as $content) {
            preg_match_all('/t3:\/\/page\?uid=(\d+)/', $content['bodytext'], $matches);
            foreach ($matches[1] as $targetUid) {
                $links[] = [
                    'source' => $content['pid'],
                    'target' => (int)$targetUid
                ];
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
}