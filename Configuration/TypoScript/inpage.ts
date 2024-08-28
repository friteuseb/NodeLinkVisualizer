# Réinitialisation de la page
page >
page = PAGE
page {
    # Configuration de base
    config {
        no_cache = 1
        disableAllHeaderCode = 1
        additionalHeaders.10.header = Content-Type:text/html;charset=utf-8
    }
    # Structure HTML de base
    10 = FLUIDTEMPLATE
    10 {
        templateName = Show
        templateRootPaths {
            0 = EXT:node_link_visualizer/Resources/Private/Templates/
        }
        layoutRootPaths {
            0 = EXT:node_link_visualizer/Resources/Private/Layouts/
        }
        partialRootPaths {
            0 = EXT:node_link_visualizer/Resources/Private/Partials/
        }
    }

    # Inclusion des fichiers JavaScript
    includeJSFooter {
        d3js = https://d3js.org/d3.v7.min.js
        d3js.external = 1
        nodelinkvisualizer = EXT:node_link_visualizer/Resources/Public/JavaScript/node-link-visualizer.js
    }

    # Inclusion des fichiers CSS (à décommenter et ajuster selon vos besoins)
    # includeCSS {
    #     nodelinkvisualizer = EXT:node_link_visualizer/Resources/Public/CSS/node-link-visualizer.css
    # }
}

# Configuration du plugin
plugin.tx_nodelinkvisualizer {
    view {
        templateRootPaths {
            0 = EXT:node_link_visualizer/Resources/Private/Templates/
        }
        partialRootPaths {
            0 = EXT:node_link_visualizer/Resources/Private/Partials/
        }
        layoutRootPaths {
            0 = EXT:node_link_visualizer/Resources/Private/Layouts/
        }
    }
    persistence {
        storagePid = 86
    }
}

# Définition du Content Object pour le plugin
tt_content.list.20.nodelinkvisualizer_pi1 =< plugin.tx_nodelinkvisualizer
