// ApacheLogTable.jsx
import React from 'react';
import CustomTable from './CustomTable';

const ApacheLogTable = ({ data }) => {
   // Определите столбцы для react-table
   const columns = React.useMemo(
      () => [
         { Header: 'IP', accessor: 'ip' },
         { Header: 'Identity', accessor: 'identity' },
         { Header: 'User ID', accessor: 'userid' },
         { Header: 'Datetime', accessor: 'datetime' },
         { Header: 'Timezone', accessor: 'timezone' },
         { Header: 'Method', accessor: 'method' },
         { Header: 'URL', accessor: 'url' },
         { Header: 'Protocol', accessor: 'protocol' },
         { Header: 'Status', accessor: 'status' },
         { Header: 'Size', accessor: 'size' },
         { Header: 'Referer', accessor: 'referer' },
         { Header: 'User Agent', accessor: 'user_agent' },
      ],
      []
   );

   return (
      <div>
         <CustomTable columns={columns} data={data} />
      </div>
   );
};

export default ApacheLogTable;
