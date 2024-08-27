document.addEventListener('DOMContentLoaded', function() {
    const visualizer = document.getElementById('node-link-visualizer');
    const debugMessages = document.getElementById('debug-messages');

    function debug(message) {
        if (debugMessages) {
            const p = document.createElement('p');
            p.textContent = message;
            debugMessages.appendChild(p);
        }
        console.log(message);
    }

    if (!visualizer) {
        debug("Élément visualizer non trouvé");
        return;
    }

    let nodes, links, displayMode;
    try {
        nodes = JSON.parse(visualizer.dataset.nodes);
        links = JSON.parse(visualizer.dataset.links);
        displayMode = visualizer.dataset.displayMode;
    } catch (error) {
        debug("Erreur lors du parsing des données : " + error.message);
        return;
    }

    if (!Array.isArray(nodes) || !Array.isArray(links)) {
        debug("Les données de nœuds ou de liens ne sont pas des tableaux valides");
        return;
    }

    debug(`Nombre de nœuds : ${nodes.length}, Nombre de liens : ${links.length}`);

    // Set up SVG
    const width = 800;
    const height = 600;
    const svg = d3.select('#node-link-visualizer')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .call(d3.zoom().on('zoom', (event) => {
            g.attr('transform', event.transform);
        }));

    const g = svg.append('g');

    debug("SVG créé avec zoom");

    // Create a force simulation
    const simulation = d3.forceSimulation(nodes)
        .force('link', d3.forceLink(links).id(d => d.id))
        .force('charge', d3.forceManyBody())
        .force('center', d3.forceCenter(width / 2, height / 2));

    debug("Simulation de force créée");

    // Draw links
    const link = g.append('g')
        .selectAll('line')
        .data(links)
        .enter().append('line')
        .attr('stroke', '#999')
        .attr('stroke-opacity', 0.6);

    debug("Liens dessinés");

    // Draw nodes
    const node = g.append('g')
        .selectAll('circle')
        .data(nodes)
        .enter().append('circle')
        .attr('r', 5)
        .attr('fill', '#69b3a2')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended));

    debug("Nœuds dessinés avec fonction de glisser-déposer");

    // Add labels to nodes
    const label = g.append('g')
        .selectAll('text')
        .data(nodes)
        .enter().append('text')
        .text(d => d.name)
        .attr('font-size', 12)
        .attr('dx', 12)
        .attr('dy', 4);

    debug("Étiquettes ajoutées");

    // Update positions on each tick of the simulation
    simulation.on('tick', () => {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);

        node
            .attr('cx', d => d.x)
            .attr('cy', d => d.y);

        label
            .attr('x', d => d.x)
            .attr('y', d => d.y);
    });

    debug("Simulation démarrée");

    // Add drag functions
    function dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }

    // Add hover effects
    node.on('mouseover', function(event, d) {
        d3.select(this).attr('r', 8);
    }).on('mouseout', function(event, d) {
        d3.select(this).attr('r', 5);
    });

    debug("Effets de survol ajoutés");
});