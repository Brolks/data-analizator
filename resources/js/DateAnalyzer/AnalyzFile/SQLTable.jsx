// AnalyzFile/SQLTable.jsx
import React from 'react';
import CustomTable from './CustomTable';


const SQLTable = ({ columns, data }) => {

    return (
        <div>
            <CustomTable columns={columns} data={data}  />
        </div>
    );
};

export default SQLTable;

