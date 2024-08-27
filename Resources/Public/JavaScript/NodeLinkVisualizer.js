document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('node-link-visualizer');
    const nodes = JSON.parse(container.dataset.nodes);
    const links = JSON.parse(container.dataset.links);

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

    const simulation = d3.forceSimulation(nodes)
        .force('link', d3.forceLink(links).id(d => d.id))
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(width / 2, height / 2));

    const link = g.append('g')
        .selectAll('line')
        .data(links)
        .enter().append('line')
        .attr('stroke', '#999')
        .attr('stroke-opacity', 0.6)
        .attr('stroke-width', 2);

    const node = g.append('g')
        .selectAll('g')
        .data(nodes)
        .enter().append('g')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended));

    node.append('circle')
        .attr('r', 5)
        .attr('fill', '#69b3a2');

    node.append('text')
        .attr('dx', 12)
        .attr('dy', '.35em')
        .text(d => d.name);

    node.append('title')
        .text(d => d.name);

    simulation.on('tick', () => {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);

        node
            .attr('transform', d => `translate(${d.x},${d.y})`);
    });

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

    // Hover effect
    node.on('mouseover', function(event, d) {
        d3.select(this).select('circle').attr('r', 8);
        // Add more detailed information here
    }).on('mouseout', function(event, d) {
        d3.select(this).select('circle').attr('r', 5);
    });

    // Simple legend
    const legend = svg.append('g')
        .attr('transform', 'translate(10, 10)');

    legend.append('circle')
        .attr('r', 5)
        .attr('fill', '#69b3a2')
        .attr('cx', 10)
        .attr('cy', 10);

    legend.append('text')
        .attr('x', 20)
        .attr('y', 15)
        .text('Page');

    // Basic search functionality
    const searchInput = d3.select('#node-link-visualizer')
        .insert('input', ':first-child')
        .attr('type', 'text')
        .attr('placeholder', 'Search nodes...')
        .style('margin-bottom', '10px');

    searchInput.on('input', function() {
        const searchTerm = this.value.toLowerCase();
        node.each(function(d) {
            const isMatch = d.name.toLowerCase().includes(searchTerm);
            d3.select(this).select('circle')
                .attr('fill', isMatch ? 'red' : '#69b3a2')
                .attr('r', isMatch ? 8 : 5);
        });
    });
});