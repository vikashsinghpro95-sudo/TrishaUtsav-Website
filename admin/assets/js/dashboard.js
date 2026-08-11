/**
 * Admin Dashboard Module - SaaS UI
 */

const Dashboard = {
    chartInstance: null,
    salesData: [],

    async init() {
        try {
            const res = await Api.get('/admin/dashboard');
            if (res.success && res.data) {
                this.renderKPIs(res.data);
                this.renderRecentOrders(res.data.recent_orders || []);
                this.salesData = res.data.sales_over_time || [];
                this.renderSalesTrend();

                // Listen for theme changes to redraw chart
                window.addEventListener('theme-changed', () => {
                    this.renderSalesTrend();
                });
            }
        } catch (e) {
            if (window.Utils && Utils.showToast) {
                Utils.showToast("Failed to retrieve dashboard metrics: " + e.message, "error");
            }
        }
    },

    renderKPIs(data) {
        document.getElementById('kpi-sales').innerText = Utils.formatCurrency(data.total_sales);
        document.getElementById('kpi-today-sales').innerText = Utils.formatCurrency(data.today_sales);
        document.getElementById('kpi-orders').innerText = data.total_orders;
        document.getElementById('kpi-pending').innerText = data.pending_orders;
        document.getElementById('kpi-customers').innerText = data.total_customers;
        document.getElementById('kpi-low-stock').innerText = data.low_stock_products;

        // Color code low stock warnings
        const stockCard = document.getElementById('kpi-low-stock-card');
        if (data.low_stock_products > 0 && stockCard) {
            stockCard.classList.remove('bg-white', 'dark:bg-slate-800', 'border-slate-200', 'dark:border-slate-700');
            stockCard.classList.add('bg-red-50', 'dark:bg-red-900/10', 'border-red-200', 'dark:border-red-900/50');
        }
    },

    renderRecentOrders(orders) {
        const list = document.getElementById('recent-orders-list');
        if (!list) return;

        if (orders.length === 0) {
            list.innerHTML = `
                <li class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    No orders recorded yet.
                </li>
            `;
            return;
        }

        let html = '';
        orders.forEach(ord => {
            const date = new Date(ord.created_at);
            
            // Format relative time (e.g., "2 hours ago")
            const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
            const daysDifference = Math.round((date - new Date()) / (1000 * 60 * 60 * 24));
            let dateStr = rtf.format(daysDifference, 'day');
            if (daysDifference === 0) {
                const hours = Math.round((date - new Date()) / (1000 * 60 * 60));
                if (hours === 0) dateStr = 'Just now';
                else dateStr = rtf.format(hours, 'hour');
            }

            let statusClass = 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300';
            let icon = 'ph-clock';
            if (ord.order_status === 'delivered') {
                statusClass = 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                icon = 'ph-check-circle';
            }
            if (ord.order_status === 'pending') {
                statusClass = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
                icon = 'ph-hourglass-high';
            }
            if (ord.order_status === 'shipped') {
                statusClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                icon = 'ph-truck';
            }
            if (ord.order_status === 'cancelled') {
                statusClass = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                icon = 'ph-x-circle';
            }

            const initial = (ord.first_name || 'U').charAt(0).toUpperCase();

            html += `
                <li class="p-3 sm:p-4 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors group">
                    <a href="/admin/order-detail.php?id=${ord.id}" class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 sm:space-x-4">
                            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-semibold flex-shrink-0">
                                ${initial}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                    ${ord.first_name} ${ord.last_name}
                                </p>
                                <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span class="font-medium">#${ord.order_number}</span>
                                    <span>&bull;</span>
                                    <span>${dateStr}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end flex-shrink-0">
                            <span class="text-sm font-bold text-slate-900 dark:text-white mb-1">
                                ${Utils.formatCurrency(ord.total)}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium tracking-wide uppercase ${statusClass}">
                                <i class="ph ${icon} mr-1"></i> ${ord.order_status}
                            </span>
                        </div>
                    </a>
                </li>
            `;
        });
        list.innerHTML = html;
    },

    renderSalesTrend() {
        const ctx = document.getElementById('salesTrendChart');
        if (!ctx || this.salesData.length === 0) return;

        const labels = this.salesData.map(t => t.month);
        const sales = this.salesData.map(t => parseFloat(t.sales));

        if (this.chartInstance) {
            this.chartInstance.destroy();
        }

        // Determine Theme Colors
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#334155' : '#f1f5f9'; // slate-700 : slate-100
        const textColor = isDark ? '#94a3b8' : '#64748b'; // slate-400 : slate-500
        const primaryColor = '#dc2626'; // primary-600
        
        let gradient = null;
        if (ctx.getContext) {
            const context = ctx.getContext('2d');
            gradient = context.createLinearGradient(0, 0, 0, 400);
            if (isDark) {
                gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
                gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
            } else {
                gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
                gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
            }
        }

        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = textColor;

        this.chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: sales,
                    borderColor: primaryColor,
                    backgroundColor: gradient || primaryColor,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4, // Smooth curves
                    pointBackgroundColor: isDark ? '#1e293b' : '#ffffff', // slate-800 or white
                    pointBorderColor: primaryColor,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#ffffff',
                        titleColor: isDark ? '#f8fafc' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 4,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: {
                            color: gridColor,
                            drawTicks: false,
                        },
                        ticks: {
                            padding: 10,
                            callback: function(value) {
                                if (value >= 1000) {
                                    return '₹' + (value / 1000) + 'k';
                                }
                                return '₹' + value;
                            }
                        }
                    },
                    x: {
                        border: { display: false },
                        grid: {
                            display: false,
                            drawTicks: false,
                        },
                        ticks: {
                            padding: 10,
                        }
                    }
                }
            }
        });
    }
};

window.Dashboard = Dashboard;
document.addEventListener('DOMContentLoaded', () => {
    Dashboard.init();
});
