// AnalyzFile.jsx
import React, { useContext, useState, useEffect } from 'react';
import FileContext from './FileContext';
import SQLTable from './AnalyzFile/SQLTable';
import ApacheLogTable from './AnalyzFile/ApacheLogTable';
import ApacheErrorLogTable from './AnalyzFile/ApacheErrorLogTable';

const generateColumns = (fields) => {
   return fields.map(field => ({
      Header: field.name,
      accessor: field.name,
      // Добавьте другие свойства столбца при необходимости
   }));
};


const generateTableData = (tableData) => {
   if (!tableData) {
      return [];
   }
   return tableData;
};


const AnalyzFile = () => {
   const { selectedFileData } = useContext(FileContext);
   const [activeTab, setActiveTab] = useState(null);

   useEffect(() => {
       // Устанавливаем активную вкладку, когда selectedFileData загружены
       if (selectedFileData && selectedFileData.file.file_type === 'sql') {
           const firstTableName = Object.keys(selectedFileData.data.tables)[0];
           setActiveTab(firstTableName);
       }
   }, [selectedFileData]);

   return (
       <div>
           {selectedFileData && (
               <div>
                   <h3>Данные файла:</h3>
                   {selectedFileData.file.file_type === 'sql' ? (
                       <div>
                           {/* Вкладки */}
                           <div className="tabs">
                               {Object.keys(selectedFileData.data.tables).map(tableName => (
                                   <button
                                       key={tableName}
                                       className={activeTab === tableName ? 'active' : ''}
                                       onClick={() => setActiveTab(tableName)}
                                   >
                                       {tableName}
                                   </button>
                               ))}
                           </div>
                           {/* Содержимое вкладок */}
                           {Object.entries(selectedFileData.data.tables).map(([tableName, tableInfo]) => (
                               <div
                                   key={tableName}
                                   className={activeTab === tableName ? 'tab-content active' : 'tab-content'}
                               >
                                   <h4>Таблица: {tableName}</h4>
                                   <SQLTable
                                       columns={generateColumns(tableInfo.fields)}
                                       data={generateTableData(selectedFileData.data.inserts[tableName].data)}
                                   />
                               </div>
                           ))}
                       </div>
                   ) : selectedFileData.file.file_type === 'apache_access' ? (
                       <ApacheLogTable data={selectedFileData.data} />
                   ) : selectedFileData.file.file_type === 'apache_errors' ? (
                       <ApacheErrorLogTable data={selectedFileData.data} />
                   ) : (
                       // Для остальных типов файлов показываем JSON
                       <pre style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-all' }}>
                           {JSON.stringify(selectedFileData, null, 2)}
                       </pre>
                   )}
               </div>
           )}
       </div>
   );
};

export default AnalyzFile;
