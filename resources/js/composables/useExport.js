import { ref } from 'vue';
import * as XLSX from 'xlsx';

export function useExport() {
  const exporting = ref(false);

  /**
   * Exporta dados para Excel
   * @param {Array} data - Array de objetos com os dados
   * @param {String} fileName - Nome do arquivo (sem extensão)
   * @param {Array} columns - Array de objetos {key, label} para mapear colunas
   */
  const exportToExcel = (data, fileName = 'export', columns = null) => {
    try {
      exporting.value = true;

      // Se columns for fornecido, mapear os dados
      let exportData = data;
      if (columns && columns.length > 0) {
        exportData = data.map(item => {
          const row = {};
          columns.forEach(col => {
            // Suporte para campos aninhados (ex: 'grupo.nome')
            const value = col.key.split('.').reduce((obj, key) => obj?.[key], item);
            row[col.label] = value || '';
          });
          return row;
        });
      }

      // Criar worksheet e workbook
      const ws = XLSX.utils.json_to_sheet(exportData);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Dados');

      // Gerar e baixar arquivo
      const timestamp = new Date().toISOString().split('T')[0];
      XLSX.writeFile(wb, `${fileName}_${timestamp}.xlsx`);

    } catch (error) {
      console.error('Erro ao exportar para Excel:', error);
      alert('Erro ao exportar dados. Tente novamente.');
    } finally {
      exporting.value = false;
    }
  };

  /**
   * Exporta dados para CSV
   * @param {Array} data - Array de objetos com os dados
   * @param {String} fileName - Nome do arquivo (sem extensão)
   * @param {Array} columns - Array de objetos {key, label} para mapear colunas
   */
  const exportToCSV = (data, fileName = 'export', columns = null) => {
    try {
      exporting.value = true;

      let exportData = data;
      if (columns && columns.length > 0) {
        exportData = data.map(item => {
          const row = {};
          columns.forEach(col => {
            const value = col.key.split('.').reduce((obj, key) => obj?.[key], item);
            row[col.label] = value || '';
          });
          return row;
        });
      }

      // Criar CSV
      const headers = Object.keys(exportData[0] || {});
      const csv = [
        headers.join(','),
        ...exportData.map(row => 
          headers.map(header => {
            const value = String(row[header] || '').replace(/"/g, '""');
            return `"${value}"`;
          }).join(',')
        )
      ].join('\n');

      // Criar blob e baixar
      const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      const timestamp = new Date().toISOString().split('T')[0];
      
      link.setAttribute('href', url);
      link.setAttribute('download', `${fileName}_${timestamp}.csv`);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

    } catch (error) {
      console.error('Erro ao exportar para CSV:', error);
      alert('Erro ao exportar dados. Tente novamente.');
    } finally {
      exporting.value = false;
    }
  };

  return {
    exporting,
    exportToExcel,
    exportToCSV
  };
}
