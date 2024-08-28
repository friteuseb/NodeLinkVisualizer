<?php
namespace TalanHdf\NodeLinkVisualizer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentAnalyzerMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        $logFile = GeneralUtility::getFileAbsFileName('typo3temp/logs/node_link_visualizer.log');
        file_put_contents($logFile, "Middleware exécuté\n", FILE_APPEND);

        $pageId = $request->getAttribute('routing')->getPageId();
        file_put_contents($logFile, "Page ID: $pageId\n", FILE_APPEND);

        $pagesToAnalyze = [86, 476];
        if (!in_array($pageId, $pagesToAnalyze)) {
            file_put_contents($logFile, "Page non analysée\n", FILE_APPEND);
            return $response;
        }

        file_put_contents($logFile, "Analyse de la page en cours\n", FILE_APPEND);

        $content = $response->getBody()->__toString();
        file_put_contents($logFile, "Contenu de la page (premiers 1000 caractères) : " . substr($content, 0, 1000) . "\n", FILE_APPEND);

        $links = $this->analyzeContent($content);
        
        file_put_contents($logFile, "Liens trouvés : " . print_r($links, true) . "\n", FILE_APPEND);

        $GLOBALS['TYPO3_CONF_VARS']['USER']['node_link_visualizer_links'] = $links;
        
        return $response;
    }

    private function analyzeContent(string $content): array
    {
        $links = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);
        $anchors = $xpath->query('//a[@href]');
        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            $text = $anchor->textContent;
            $links[] = [
                'href' => $href,
                'text' => trim($text),
                'type' => $this->getLinkType($href)
            ];
        }
        return $links;
    }

    private function getLinkType(string $href): string
    {
        if (strpos($href, 'index.php?id=') !== false || preg_match('/^\/[a-zA-Z0-9-_\/]+$/', $href)) {
            return 'internal';
        } elseif (strpos($href, '://') !== false) {
            return 'external';
        } else {
            return 'unknown';
        }
    }
}