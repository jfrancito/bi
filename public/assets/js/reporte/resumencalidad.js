$(document).ready(function () {
    let filters = {
        anio: [],
        mes: [],
        centro: []
    };

    // Initialize default filters
    function initDefaults() {
        const lastYear = $('#filter-anio .btn-filter').first().data('value');
        const currentMonth = new Date().getMonth() + 1; // getMonth() returns 0-11

        if (lastYear) {
            filters.anio = [lastYear.toString()];
            $(`#filter-anio .btn-filter[data-value="${lastYear}"]`).addClass('active');
        }

        // Try to select the current actual month
        if ($(`#filter-mes .btn-filter[data-value="${currentMonth}"]`).length) {
            filters.mes = [currentMonth.toString()];
            $(`#filter-mes .btn-filter[data-value="${currentMonth}"]`).addClass('active');
        } else {
            // Fallback to the last available month if current month has no data
            const lastAvailableMonth = $('#filter-mes .btn-filter').last().data('value');
            if (lastAvailableMonth) {
                filters.mes = [lastAvailableMonth.toString()];
                $(`#filter-mes .btn-filter[data-value="${lastAvailableMonth}"]`).addClass('active');
            }
        }

        // Select all centers by default
        $('#filter-centro .btn-filter').each(function () {
            const val = $(this).data('value').toString();
            filters.centro.push(val);
            $(this).addClass('active');
        });
    }

    initDefaults();

    // Initialize report
    loadReport();

    // Filter button clicks
    $('.btn-filter').on('click', function () {
        const $btn = $(this);
        const filterType = $btn.parent().attr('id').replace('filter-', '');
        const value = $btn.data('value').toString();

        if ($btn.hasClass('active')) {
            $btn.removeClass('active');
            filters[filterType] = filters[filterType].filter(item => item !== value);
        } else {
            $btn.addClass('active');
            filters[filterType].push(value);
        }

        loadReport();
    });

    // Clear filters
    $('.clear-filter').on('click', function () {
        const filterType = $(this).data('filter');
        filters[filterType] = [];
        $(`#filter-${filterType} .btn-filter`).removeClass('active');
        loadReport();
    });

    // Export summary table to CSV
    $('#btn-export-summary').on('click', function () {
        let csv = [];
        // Add UTF-8 BOM for Excel
        csv.push('\uFEFF');

        // Header
        let rows = $('#tbl-resumen-calidad thead tr');
        rows.each(function () {
            let row = [];
            $(this).find('th').each(function () {
                row.push('"' + $(this).text().trim() + '"');
            });
            csv.push(row.join(';'));
        });

        // Body
        $('#tbl-resumen-calidad tbody tr').each(function () {
            if ($(this).is(':visible') || true) { // Export all even if hidden
                let row = [];
                $(this).find('td').each(function (index) {
                    let text = $(this).text().trim().replace(/expand_more|chevron_right/g, '');
                    row.push('"' + text + '"');
                });
                csv.push(row.join(';'));
            }
        });

        // Footer
        $('#tbl-resumen-calidad tfoot tr').each(function () {
            let row = [];
            $(this).find('td').each(function () {
                row.push('"' + $(this).text().trim() + '"');
            });
            csv.push(row.join(';'));
        });

        const csvString = csv.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", "reporte_resumen_calidad.csv");
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    function loadReport() {
        const url = window.baseUrl + '/ajax-listado-resumen-calidad';
        const data = {
            anio: filters.anio.join(','),
            mes: filters.mes.join(','),
            centro: filters.centro.join(','),
            _token: $('#token').val()
        };

        // Show loading for both
        $('#tbl-resumen-calidad tbody, #tbl-resumen-solo-calidad tbody').html('<tr><td colspan="8" class="text-center">Cargando datos...</td></tr>');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                renderReport(response);
                renderQualitySummary(response);
            },
            error: function (xhr) {
                console.error(xhr);
                $('#tbl-resumen-calidad tbody, #tbl-resumen-solo-calidad tbody').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos</td></tr>');
            }
        });

        // Load third table (only by year)
        loadMonthlyReport();
        // Load fourth table (only by year)
        loadPriceQualityReport();
    }

    function loadMonthlyReport() {
        if (filters.anio.length === 0) return;

        const url = window.baseUrl + '/ajax-resumen-mensual';
        const data = {
            anio: filters.anio.join(','),
            _token: $('#token').val()
        };

        $('#tbl-resumen-mensual tbody').html('<tr><td colspan="30" class="text-center">Cargando comparativo mensual...</td></tr>');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                renderMonthlyReport(response);
            },
            error: function (xhr) {
                console.error(xhr);
                $('#tbl-resumen-mensual tbody').html('<tr><td colspan="30" class="text-center text-danger">Error al cargar comparativo</td></tr>');
            }
        });
    }

    function loadPriceQualityReport() {
        if (filters.anio.length === 0) return;

        const url = window.baseUrl + '/ajax-resumen-calidad-mensual';
        const data = {
            anio: filters.anio.join(','),
            _token: $('#token').val()
        };

        $('#tbl-resumen-precios-calidad tbody').html('<tr><td colspan="15" class="text-center">Cargando comparativo de precios...</td></tr>');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                renderPriceQualityReport(response);
            },
            error: function (xhr) {
                console.error(xhr);
                $('#tbl-resumen-precios-calidad tbody').html('<tr><td colspan="15" class="text-center text-danger">Error al cargar comparativo</td></tr>');
            }
        });
    }

    function renderPriceQualityReport(data) {
        const $tbody = $('#tbl-resumen-precios-calidad tbody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.append('<tr><td colspan="15" class="text-center">No se encontraron registros</td></tr>');
            return;
        }

        const organized = {};
        const qualities = new Set();
        data.forEach(item => {
            qualities.add(item.calidad);
            if (!organized[item.calidad]) organized[item.calidad] = {};
            organized[item.calidad][item.mes_numero_compra] = {
                h: parseFloat(item.total_peso_humedo) || 0,
                imp: parseFloat(item.total_importe) || 0
            };
        });

        const sortedQualities = Array.from(qualities).sort();

        sortedQualities.forEach(calidadName => {
            let rowHtml = `<tr data-calidad="${calidadName}" style="cursor:pointer;" title="Doble clic para exportar detalle anual de esta calidad">`;
            rowHtml += `<td class="fw-bold text-primary">${calidadName}</td>`;

            let yearTotalH = 0;
            let yearTotalImp = 0;

            for (let m = 1; m <= 12; m++) {
                const monthInfo = organized[calidadName][m] || { h: 0, imp: 0 };
                const puH = monthInfo.h > 0 ? monthInfo.imp / monthInfo.h : 0;
                yearTotalH += monthInfo.h;
                yearTotalImp += monthInfo.imp;

                rowHtml += `<td class="text-end col-pq-month-${m}" data-month="${m}">${formatNumber(puH, 2)}</td>`;
            }

            const yearAvgPUH = yearTotalH > 0 ? yearTotalImp / yearTotalH : 0;
            rowHtml += `<td class="text-end fw-bold bg-light">${formatNumber(yearAvgPUH, 2)}</td>`;
            rowHtml += `</tr>`;
            $tbody.append(rowHtml);
        });

        renderPriceQualityFooter(organized, sortedQualities);

        // Events
        $('#tbl-resumen-precios-calidad tbody tr').on('dblclick', function () {
            exportToExcel(null, null, $(this).data('calidad'));
        });

        $('#tbl-resumen-precios-calidad tbody td[data-month]').on('dblclick', function (e) {
            e.stopPropagation();
            const calidad = $(this).parent().data('calidad');
            const month = $(this).data('month');
            const url = window.baseUrl + '/ajax-exportar-detalle-resumen-calidad';
            const params = $.param({ anio: filters.anio.join(','), mes: month, calidad: calidad, _token: $('#token').val() });
            window.location.href = url + '?' + params;
        });

        applyPQMonthlyVisibility();
    }

    function renderPriceQualityFooter(organized, qualities) {
        const $tfootRow = $('#price-quality-footer');
        $tfootRow.empty();
        $tfootRow.append('<td class="text-center bg-light">PROM. GRAL</td>');

        let grandTotalH = 0, grandTotalImp = 0;
        let monthlyTotals = {};

        qualities.forEach(q => {
            for (let m = 1; m <= 12; m++) {
                if (!monthlyTotals[m]) monthlyTotals[m] = { h: 0, imp: 0 };
                const d = organized[q][m] || { h: 0, imp: 0 };
                monthlyTotals[m].h += d.h;
                monthlyTotals[m].imp += d.imp;
                grandTotalH += d.h;
                grandTotalImp += d.imp;
            }
        });

        for (let m = 1; m <= 12; m++) {
            const mH = monthlyTotals[m].h;
            const mPU = mH > 0 ? monthlyTotals[m].imp / mH : 0;
            $tfootRow.append(`<td class="col-pq-month-${m} bg-light">${formatNumber(mPU, 2)}</td>`);
        }

        const avgPU = grandTotalH > 0 ? grandTotalImp / grandTotalH : 0;
        $tfootRow.append(`<td class="bg-light">${formatNumber(avgPU, 2)}</td>`);
    }

    $('.chk-pq-month-toggle').on('change', function () { applyPQMonthlyVisibility(); });
    $('#btn-pq-month-all').on('click', function () { $('.chk-pq-month-toggle').prop('checked', true).trigger('change'); });
    $('#btn-pq-month-none').on('click', function () { $('.chk-pq-month-toggle').prop('checked', false).trigger('change'); });

    function applyPQMonthlyVisibility() {
        $('.chk-pq-month-toggle').each(function () {
            const m = $(this).val();
            if ($(this).is(':checked')) $(`.col-pq-month-${m}`).show();
            else $(`.col-pq-month-${m}`).hide();
        });
    }

    setupExportButton('btn-export-price-quality', 'tbl-resumen-precios-calidad', 'reporte_precios_calidad_mensual');

    function renderMonthlyReport(data) {
        const $tbody = $('#tbl-resumen-mensual tbody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.append('<tr><td colspan="30" class="text-center">No se encontraron registros</td></tr>');
            return;
        }

        // Organize data: { Centro: { mes: { totalH, totalImp } } }
        const organized = {};
        const centers = new Set();
        data.forEach(item => {
            centers.add(item.centro_acopio_nombre);
            if (!organized[item.centro_acopio_nombre]) organized[item.centro_acopio_nombre] = {};
            organized[item.centro_acopio_nombre][item.mes_numero_compra] = {
                h: parseFloat(item.total_peso_humedo) || 0,
                imp: parseFloat(item.total_importe) || 0
            };
        });

        const sortedCenters = Array.from(centers).sort();

        sortedCenters.forEach(centroName => {
            let rowHtml = `<tr data-centro="${centroName}" style="cursor:pointer;" title="Doble clic para exportar detalle anual del centro">`;
            rowHtml += `<td class="fw-bold bg-light">${centroName}</td>`;

            let yearTotalH = 0;
            let yearTotalImp = 0;

            for (let m = 1; m <= 12; m++) {
                const monthInfo = organized[centroName][m] || { h: 0, imp: 0 };
                const puH = monthInfo.h > 0 ? monthInfo.imp / monthInfo.h : 0;
                yearTotalH += monthInfo.h;
                yearTotalImp += monthInfo.imp;

                rowHtml += `
                    <td class="text-end col-month-${m}" data-month="${m}">${formatNumber(monthInfo.h)}</td>
                    <td class="text-end col-month-${m} text-muted bg-light-subtle" data-month="${m}">${formatNumber(puH, 2)}</td>
                `;
            }

            const yearPUH = yearTotalH > 0 ? yearTotalImp / yearTotalH : 0;
            rowHtml += `
                <td class="text-end fw-bold bg-light">${formatNumber(yearTotalH)}</td>
                <td class="text-end fw-bold bg-light">${formatNumber(yearPUH, 2)}</td>
            `;
            rowHtml += `</tr>`;
            $tbody.append(rowHtml);
        });

        // Generate Totals Footer
        renderMonthlyFooter(organized, sortedCenters);

        // Add events
        $('#tbl-resumen-mensual tbody tr').on('dblclick', function () {
            const centro = $(this).data('centro');
            exportToExcel(centro); // Todo el año para ese centro
        });

        $('#tbl-resumen-mensual tbody td[data-month]').on('dblclick', function (e) {
            e.stopPropagation();
            const centro = $(this).parent().data('centro');
            const month = $(this).data('month');
            // Export specific month
            const url = window.baseUrl + '/ajax-exportar-detalle-resumen-calidad';
            const params = $.param({
                anio: filters.anio.join(','),
                mes: month,
                centro: centro,
                _token: $('#token').val()
            });
            window.location.href = url + '?' + params;
        });

        // Apply column visibility
        applyMonthlyVisibility();
    }

    function renderMonthlyFooter(organized, centers) {
        const $tfootRow = $('#monthly-footer');
        $tfootRow.empty();
        $tfootRow.append('<td class="text-center bg-light">TOTALES</td>');

        let grandYearTotalH = 0;
        let grandYearTotalImp = 0;
        let monthlyTotals = {}; // { month: { h, imp } }

        centers.forEach(centro => {
            for (let m = 1; m <= 12; m++) {
                if (!monthlyTotals[m]) monthlyTotals[m] = { h: 0, imp: 0 };
                const d = organized[centro][m] || { h: 0, imp: 0 };
                monthlyTotals[m].h += d.h;
                monthlyTotals[m].imp += d.imp;
                grandYearTotalH += d.h;
                grandYearTotalImp += d.imp;
            }
        });

        for (let m = 1; m <= 12; m++) {
            const mH = monthlyTotals[m].h;
            const mPU = mH > 0 ? monthlyTotals[m].imp / mH : 0;
            $tfootRow.append(`
                <td class="col-month-${m} bg-light">${formatNumber(mH)}</td>
                <td class="col-month-${m} bg-light text-muted">${formatNumber(mPU, 2)}</td>
            `);
        }

        const grandPU = grandYearTotalH > 0 ? grandYearTotalImp / grandYearTotalH : 0;
        $tfootRow.append(`
            <td class="bg-light">${formatNumber(grandYearTotalH)}</td>
            <td class="bg-light">${formatNumber(grandPU, 2)}</td>
        `);
    }

    // Column toggler
    $('.chk-month-toggle').on('change', function () {
        applyMonthlyVisibility();
    });

    $('#btn-month-all').on('click', function () {
        $('.chk-month-toggle').prop('checked', true).trigger('change');
    });

    $('#btn-month-none').on('click', function () {
        $('.chk-month-toggle').prop('checked', false).trigger('change');
    });

    function applyMonthlyVisibility() {
        $('.chk-month-toggle').each(function () {
            const month = $(this).val();
            if ($(this).is(':checked')) {
                $(`.col-month-${month}`).show();
            } else {
                $(`.col-month-${month}`).hide();
            }
        });
    }

    setupExportButton('btn-export-monthly', 'tbl-resumen-mensual', 'reporte_comparativo_mensual');

    // Export logic for any table
    function setupExportButton(btnId, tableId, filename) {
        $(`#${btnId}`).off('click').on('click', function () {
            let csv = [];
            csv.push('\uFEFF');

            $(`#${tableId} thead tr`).each(function () {
                let row = [];
                $(this).find('th').each(function () { row.push('"' + $(this).text().trim() + '"'); });
                csv.push(row.join(';'));
            });

            $(`#${tableId} tbody tr`).each(function () {
                let row = [];
                $(this).find('td').each(function () {
                    let text = $(this).text().trim().replace(/expand_more|chevron_right/g, '');
                    row.push('"' + text + '"');
                });
                csv.push(row.join(';'));
            });

            $(`#${tableId} tfoot tr`).each(function () {
                let row = [];
                $(this).find('td').each(function () { row.push('"' + $(this).text().trim() + '"'); });
                csv.push(row.join(';'));
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.setAttribute("href", URL.createObjectURL(blob));
            link.setAttribute("download", `${filename}.csv`);
            link.click();
        });
    }

    setupExportButton('btn-export-summary', 'tbl-resumen-calidad', 'reporte_acopio_centro_variedad');
    setupExportButton('btn-export-summary-calidad', 'tbl-resumen-solo-calidad', 'reporte_acopio_calidad');

    function renderReport(data) {
        const $tbody = $('#tbl-resumen-calidad tbody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.append('<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>');
            resetTotals();
            return;
        }

        // Group data by Centro then Variedad
        const groupedData = {};
        let grandTotalH = 0, grandTotalS = 0, grandTotalImporte = 0;

        data.forEach(item => {
            const h = parseFloat(item.total_peso_humedo) || 0;
            const s = parseFloat(item.total_peso_seco) || 0;
            const imp = parseFloat(item.total_importe) || 0;

            grandTotalH += h; grandTotalS += s; grandTotalImporte += imp;

            if (!groupedData[item.centro_acopio_nombre]) {
                groupedData[item.centro_acopio_nombre] = { totalH: 0, totalS: 0, totalImporte: 0, variedades: {} };
            }
            groupedData[item.centro_acopio_nombre].totalH += h;
            groupedData[item.centro_acopio_nombre].totalS += s;
            groupedData[item.centro_acopio_nombre].totalImporte += imp;

            if (!groupedData[item.centro_acopio_nombre].variedades[item.variedad_arroz]) {
                groupedData[item.centro_acopio_nombre].variedades[item.variedad_arroz] = { totalH: 0, totalS: 0, totalImporte: 0, items: [] };
            }
            groupedData[item.centro_acopio_nombre].variedades[item.variedad_arroz].totalH += h;
            groupedData[item.centro_acopio_nombre].variedades[item.variedad_arroz].totalS += s;
            groupedData[item.centro_acopio_nombre].variedades[item.variedad_arroz].totalImporte += imp;
            groupedData[item.centro_acopio_nombre].variedades[item.variedad_arroz].items.push(item);
        });

        // Render rows level 1, 2, 3
        Object.keys(groupedData).sort().forEach(centroName => {
            const centro = groupedData[centroName];
            const pUnitH = centro.totalH > 0 ? centro.totalImporte / centro.totalH : 0;
            const pUnitS = centro.totalS > 0 ? centro.totalImporte / centro.totalS : 0;
            const varHS = pUnitH > 0 ? ((pUnitS - pUnitH) / pUnitH) * 100 : 0;
            const centroRowId = `centro-${centroName.replace(/\s+/g, '-')}`;

            $tbody.append(`
                <tr class="centro-group bg-light-blue" data-id="${centroRowId}" data-centro="${centroName}" style="cursor: pointer;" title="Doble clic para exportar detalle">
                    <td class="fw-bold"><i class="material-symbols-outlined fs-6 align-middle exp-icon">chevron_right</i> ${centroName}</td>
                    <td></td><td></td>
                    <td class="text-end fw-bold">${formatNumber(centro.totalH)}</td>
                    <td class="text-end fw-bold">${formatNumber(pUnitH, 2)}</td>
                    <td class="text-end fw-bold">${formatNumber(centro.totalS)}</td>
                    <td class="text-end fw-bold">${formatNumber(pUnitS, 2)}</td>
                    <td class="text-end fw-bold">${formatNumber(varHS, 2)}%</td>
                </tr>
            `);

            Object.keys(centro.variedades).sort().forEach(variedadName => {
                const variedad = centro.variedades[variedadName];
                const vPUnitH = variedad.totalH > 0 ? variedad.totalImporte / variedad.totalH : 0;
                const vPUnitS = variedad.totalS > 0 ? variedad.totalImporte / variedad.totalS : 0;
                const vVarHS = vPUnitH > 0 ? ((vPUnitS - vPUnitH) / vPUnitH) * 100 : 0;
                const variedadRowId = `${centroRowId}-v-${variedadName.replace(/\s+/g, '-')}`;

                $tbody.append(`
                    <tr class="variedad-group ${centroRowId} bg-white" data-id="${variedadRowId}" data-centro="${centroName}" data-variedad="${variedadName}" style="cursor: pointer; display: none;" title="Doble clic para exportar detalle">
                        <td style="padding-left: 2rem;"></td>
                        <td class="fw-semibold"><i class="material-symbols-outlined fs-6 align-middle exp-icon">chevron_right</i> ${variedadName}</td>
                        <td></td>
                        <td class="text-end">${formatNumber(variedad.totalH)}</td>
                        <td class="text-end">${formatNumber(vPUnitH, 2)}</td>
                        <td class="text-end">${formatNumber(variedad.totalS)}</td>
                        <td class="text-end">${formatNumber(vPUnitS, 2)}</td>
                        <td class="text-end">${formatNumber(vVarHS, 2)}%</td>
                    </tr>
                `);

                variedad.items.forEach(item => {
                    const iH = parseFloat(item.total_peso_humedo) || 0, iS = parseFloat(item.total_peso_seco) || 0, iImp = parseFloat(item.total_importe) || 0;
                    const iPUnitH = iH > 0 ? iImp / iH : 0, iPUnitS = iS > 0 ? iImp / iS : 0, iVarHS = iPUnitH > 0 ? ((iPUnitS - iPUnitH) / iPUnitH) * 100 : 0;

                    $tbody.append(`
                        <tr class="calidad-row ${centroRowId} ${variedadRowId}" data-centro="${centroName}" data-variedad="${variedadName}" data-calidad="${item.calidad}" style="display: none; cursor: pointer;" title="Doble clic para exportar detalle">
                            <td colspan="2"></td>
                            <td class="text-muted">${item.calidad}</td>
                            <td class="text-end text-muted">${formatNumber(iH)}</td>
                            <td class="text-end text-muted">${formatNumber(iPUnitH, 2)}</td>
                            <td class="text-end text-muted">${formatNumber(iS)}</td>
                            <td class="text-end text-muted">${formatNumber(iPUnitS, 2)}</td>
                            <td class="text-end text-muted">${formatNumber(iVarHS, 2)}%</td>
                        </tr>
                    `);
                });
            });
        });

        // Update Totals Table 1
        const grandPUnitH = grandTotalH > 0 ? grandTotalImporte / grandTotalH : 0;
        const grandPUnitS = grandTotalS > 0 ? grandTotalImporte / grandTotalS : 0;
        const grandVarHS = grandPUnitH > 0 ? ((grandPUnitS - grandPUnitH) / grandPUnitH) * 100 : 0;

        $('#total-peso-h').text(formatNumber(grandTotalH));
        $('#total-p-unt-h').text(formatNumber(grandPUnitH, 2));
        $('#total-peso-s').text(formatNumber(grandTotalS));
        $('#total-p-unt-s').text(formatNumber(grandPUnitS, 2));
        $('#total-var-hs').text(formatNumber(grandVarHS, 2) + '%');

        // Total general row export T1
        $('#tbl-resumen-calidad tfoot tr').css('cursor', 'pointer').attr('title', 'Doble clic para exportar detalle total').off('dblclick').on('dblclick', function () {
            exportToExcel();
        });

        addTableListeners('tbl-resumen-calidad');
    }

    function renderQualitySummary(data) {
        const $tbody = $('#tbl-resumen-solo-calidad tbody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.append('<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>');
            return;
        }

        const qualityData = {};
        let qTotalH = 0, qTotalS = 0, qTotalImporte = 0;

        data.forEach(item => {
            const h = parseFloat(item.total_peso_humedo) || 0;
            const s = parseFloat(item.total_peso_seco) || 0;
            const imp = parseFloat(item.total_importe) || 0;

            qTotalH += h; qTotalS += s; qTotalImporte += imp;

            if (!qualityData[item.calidad]) {
                qualityData[item.calidad] = { totalH: 0, totalS: 0, totalImporte: 0 };
            }
            qualityData[item.calidad].totalH += h;
            qualityData[item.calidad].totalS += s;
            qualityData[item.calidad].totalImporte += imp;
        });

        Object.keys(qualityData).sort().forEach(calidadName => {
            const q = qualityData[calidadName];
            const pUnitH = q.totalH > 0 ? q.totalImporte / q.totalH : 0;
            const pUnitS = q.totalS > 0 ? q.totalImporte / q.totalS : 0;
            const varHS = pUnitH > 0 ? ((pUnitS - pUnitH) / pUnitH) * 100 : 0;

            $tbody.append(`
                <tr data-calidad="${calidadName}" style="cursor: pointer;" title="Doble clic para exportar detalle">
                    <td colspan="3" class="fw-bold text-primary">${calidadName}</td>
                    <td class="text-end">${formatNumber(q.totalH)}</td>
                    <td class="text-end">${formatNumber(pUnitH, 2)}</td>
                    <td class="text-end">${formatNumber(q.totalS)}</td>
                    <td class="text-end">${formatNumber(pUnitS, 2)}</td>
                    <td class="text-end">${formatNumber(varHS, 2)}%</td>
                </tr>
            `);
        });

        // Update Totals Table 2
        const qGrandPUnitH = qTotalH > 0 ? qTotalImporte / qTotalH : 0;
        const qGrandPUnitS = qTotalS > 0 ? qTotalImporte / qTotalS : 0;
        const qGrandVarHS = qGrandPUnitH > 0 ? ((qGrandPUnitS - qGrandPUnitH) / qGrandPUnitH) * 100 : 0;

        $('#total-q-peso-h').text(formatNumber(qTotalH));
        $('#total-q-p-unt-h').text(formatNumber(qGrandPUnitH, 2));
        $('#total-q-peso-s').text(formatNumber(qTotalS));
        $('#total-q-p-unt-s').text(formatNumber(qGrandPUnitS, 2));
        $('#total-q-var-hs').text(formatNumber(qGrandVarHS, 2) + '%');

        // Total general row export T2
        $('#tbl-resumen-solo-calidad tfoot tr').css('cursor', 'pointer').attr('title', 'Doble clic para exportar detalle total').off('dblclick').on('dblclick', function () {
            exportToExcel();
        });

        // Dbl click for rows in T2
        $('#tbl-resumen-solo-calidad tbody tr').off('dblclick').on('dblclick', function () {
            exportToExcel(null, null, $(this).data('calidad'));
        });
    }

    function addTableListeners(tableId) {
        $(`#${tableId} .centro-group`).off('click').on('click', function () {
            const id = $(this).data('id');
            const $icon = $(this).find('.exp-icon');
            if ($icon.text() === 'expand_more') {
                $icon.text('chevron_right');
                $(`tr.${id}`).fadeOut();
                $(`tr.${id}.variedad-group .exp-icon`).text('chevron_right');
            } else {
                $icon.text('expand_more');
                $(`tr.${id}.variedad-group`).fadeIn();
            }
        });

        $(`#${tableId} .variedad-group`).off('click').on('click', function () {
            const id = $(this).data('id');
            const $icon = $(this).find('.exp-icon');
            if ($icon.text() === 'expand_more') {
                $icon.text('chevron_right');
                $(`tr.${id}`).fadeOut();
            } else {
                $icon.text('expand_more');
                $(`tr.${id}`).fadeIn();
            }
        });

        $(`#${tableId} tbody tr`).off('dblclick').on('dblclick', function () {
            const centro = $(this).data('centro');
            const variedad = $(this).data('variedad');
            const calidad = $(this).data('calidad');
            exportToExcel(centro, variedad, calidad);
        });
    }

    function exportToExcel(centro = null, variedad = null, calidad = null) {
        const url = window.baseUrl + '/ajax-exportar-detalle-resumen-calidad';
        const params = $.param({
            anio: filters.anio.join(','),
            mes: filters.mes.join(','),
            centro_filtro: filters.centro.join(','),
            centro: centro,
            variedad: variedad,
            calidad: calidad,
            _token: $('#token').val()
        });

        window.location.href = url + '?' + params;
    }

    function resetTotals() {
        $('#total-peso-h, #total-p-unt-h, #total-peso-s, #total-p-unt-s').text('0.00');
        $('#total-var-hs').text('0.00%');
    }

    function formatNumber(num, decimals = 2) {
        return num.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }
});
