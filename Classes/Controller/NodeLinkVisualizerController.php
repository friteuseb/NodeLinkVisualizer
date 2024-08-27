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
        $nodes = $this->getNodes();
        $links = $this->getLinks();

        $this->view->assign('nodes', json_encode($nodes));
        $this->view->assign('links', json_encode($links));

        return $this->htmlResponse();
    }

    private function getNodes(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $result = $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->execute()
            ->fetchAllAssociative();

        return array_map(function($page) {
            return [
                'id' => $page['uid'],
                'name' => $page['title']
            ];
        }, $result);
    }

    private function getLinks(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $result = $queryBuilder
            ->select('pid', 'header', 'bodytext')
            ->from('tt_content')
            ->where(
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

        return $links;
    }
}