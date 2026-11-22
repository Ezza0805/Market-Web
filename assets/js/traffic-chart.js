// assets/js/traffic-chart.js - Pure Vanilla HTML5 Canvas Chart Engine

function renderTrafficChart(canvasId, labels, dataPoints, labelName = 'Visits') {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    
    // Size canvas properly for retina displays
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);

    const width = rect.width;
    const height = rect.height;
    const padding = { top: 30, right: 25, bottom: 40, left: 45 };

    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    const maxValue = Math.max(...dataPoints, 5);
    const stepY = chartHeight / maxValue;
    const stepX = chartWidth / (labels.length - 1 || 1);

    // 1. Draw Grid Lines
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;
    ctx.fillStyle = '#94a3b8';
    ctx.font = '11px sans-serif';
    ctx.textAlign = 'right';

    const gridLinesCount = 5;
    for (let i = 0; i <= gridLinesCount; i++) {
        const val = Math.round((maxValue / gridLinesCount) * i);
        const y = padding.top + chartHeight - (val * stepY);

        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(width - padding.right, y);
        ctx.stroke();

        ctx.fillText(val.toString(), padding.left - 10, y + 4);
    }

    // 2. Draw X-axis labels
    ctx.textAlign = 'center';
    const labelSkip = labels.length > 12 ? 2 : 1;
    labels.forEach((lbl, idx) => {
        if (idx % labelSkip === 0) {
            const x = padding.left + (idx * stepX);
            ctx.fillText(lbl, x, height - padding.bottom + 20);
        }
    });

    // 3. Compute Coordinates
    const points = dataPoints.map((val, idx) => ({
        x: padding.left + (idx * stepX),
        y: padding.top + chartHeight - (val * stepY),
        val: val
    }));

    // 4. Fill Gradient Area
    const gradient = ctx.createLinearGradient(0, padding.top, 0, height - padding.bottom);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.35)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

    ctx.beginPath();
    ctx.moveTo(points[0].x, height - padding.bottom);
    points.forEach(pt => ctx.lineTo(pt.x, pt.y));
    ctx.lineTo(points[points.length - 1].x, height - padding.bottom);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();

    // 5. Draw Smooth Stroke Line
    ctx.beginPath();
    ctx.strokeStyle = '#2563eb';
    ctx.lineWidth = 3;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
    points.forEach((pt, i) => {
        if (i === 0) ctx.moveTo(pt.x, pt.y);
        else ctx.lineTo(pt.x, pt.y);
    });
    ctx.stroke();

    // 6. Draw Dots and Values
    points.forEach(pt => {
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 4, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff';
        ctx.fill();
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#2563eb';
        ctx.stroke();

        if (pt.val > 0) {
            ctx.fillStyle = '#1e293b';
            ctx.font = 'bold 10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(pt.val, pt.x, pt.y - 8);
        }
    });
}
