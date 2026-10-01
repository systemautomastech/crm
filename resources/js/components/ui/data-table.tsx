import React, { useState, useMemo } from 'react';
import { Input } from './input';
import { Button } from './button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './select';
import {
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  Search,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
} from "lucide-react";
import { cn } from "@/lib/utils";

export interface Column<T = any> {
  key: string;
  header: string;
  sortable?: boolean;
  render?: (value: any, row: T, index: number) => React.ReactNode;
  className?: string;
}

export interface DataTableProps<T = any> {
  data: T[];
  columns: Column<T>[];
  onSort?: (key: string) => void;
  sortKey?: string;
  sortDirection?: 'asc' | 'desc';
  emptyState?: React.ReactNode;
  className?: string;
  searchable?: boolean;
  searchPlaceholder?: string;
  pageSize?: number;
  showPagination?: boolean;
  rowProps?: (row: T, index: number) => React.HTMLAttributes<HTMLTableRowElement>;
}

export function DataTable<T = any>({
  data,
  columns,
  onSort,
  sortKey,
  sortDirection,
  emptyState,
  className,
  searchable = false,
  searchPlaceholder = "Filter...",
  pageSize = 10,
  showPagination = false,
  rowProps
}: DataTableProps<T>) {
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [rowsPerPage, setRowsPerPage] = useState(pageSize);

  const getSortIcon = (field: string) => {
    if (sortKey !== field) return <ArrowUpDown className="ml-2 h-3.5 w-3.5 opacity-60" />;
    return sortDirection === 'asc' 
      ? <ArrowUp className="ml-2 h-3.5 w-3.5 text-foreground" /> 
      : <ArrowDown className="ml-2 h-3.5 w-3.5 text-foreground" />;
  };

  const handleSort = (key: string, sortable?: boolean) => {
    if (sortable && onSort) {
      onSort(key);
    }
  };

  const filteredData = useMemo(() => {
    const safeData = Array.isArray(data) ? data : [];
    if (!searchable || !searchTerm) return safeData;
    return safeData.filter((row: any) => 
      columns.some(column => {
        const value = row[column.key];
        return value?.toString().toLowerCase().includes(searchTerm.toLowerCase());
      })
    );
  }, [data, searchTerm, columns, searchable]);

  const totalPages = Math.max(1, Math.ceil(filteredData.length / rowsPerPage));

  const paginatedData = useMemo(() => {
    if (!showPagination) return filteredData;
    const startIndex = (currentPage - 1) * rowsPerPage;
    return filteredData.slice(startIndex, startIndex + rowsPerPage);
  }, [filteredData, currentPage, rowsPerPage, showPagination]);

  const handlePageChange = (page: number) => {
    setCurrentPage(Math.min(Math.max(1, page), totalPages));
  };

  const handlePageSizeChange = (val: string) => {
    const nextSize = Number(val);
    setRowsPerPage(nextSize);
    setCurrentPage(1);
  };

  return (
    <div className={cn("space-y-4", className)}>
      {/* Shadcn Tasks Style Toolbar */}
      {searchable && (
        <div className="flex items-center justify-between">
          <div className="flex flex-1 items-center space-x-2">
            <div className="relative w-full max-w-xs">
              <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
              <Input
                placeholder={searchPlaceholder}
                value={searchTerm}
                onChange={(e) => {
                  setSearchTerm(e.target.value);
                  setCurrentPage(1);
                }}
                className="h-8 pl-8 text-xs sm:text-sm bg-background border-input focus-visible:ring-1"
              />
            </div>
          </div>
        </div>
      )}

      {/* Crisp bordered table container */}
      <div className="rounded-md border bg-card">
        <Table>
          <TableHeader>
            <TableRow>
              {columns.map((column) => (
                <TableHead
                  key={column.key}
                  className={cn(
                    column.sortable ? 'cursor-pointer select-none hover:text-foreground transition-colors' : '',
                    column.className || ''
                  )}
                  onClick={() => handleSort(column.key, column.sortable)}
                >
                  <div className="flex items-center">
                    <span>{column.header}</span>
                    {column.sortable && getSortIcon(column.key)}
                  </div>
                </TableHead>
              ))}
            </TableRow>
          </TableHeader>
          <TableBody>
            {paginatedData.length > 0 ? (
              paginatedData.map((row, index) => (
                <TableRow 
                  key={(row as any).id || index}
                  {...(rowProps ? rowProps(row, index) : {})}
                >
                  {columns.map((column) => (
                    <TableCell key={column.key} className={column.className}>
                      {column.render
                        ? column.render((row as any)[column.key], row, index)
                        : (row as any)[column.key] || '-'
                      }
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length} className="h-24 text-center">
                  {emptyState || (
                    <div className="flex flex-col items-center justify-center text-center">
                      <p className="text-sm text-muted-foreground">
                        {searchTerm ? 'No results found.' : 'No data available.'}
                      </p>
                    </div>
                  )}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>

      {/* Shadcn Tasks Style Pagination */}
      {showPagination && (
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4 px-2">
          <div className="text-xs sm:text-sm text-muted-foreground">
            {filteredData.length > 0 ? (
              <>
                Showing{" "}
                <span className="font-medium text-foreground">
                  {(currentPage - 1) * rowsPerPage + 1}
                </span>{" "}
                to{" "}
                <span className="font-medium text-foreground">
                  {Math.min(currentPage * rowsPerPage, filteredData.length)}
                </span>{" "}
                of{" "}
                <span className="font-medium text-foreground">
                  {filteredData.length}
                </span>{" "}
                results
              </>
            ) : (
              "0 results"
            )}
          </div>

          <div className="flex items-center space-x-6 lg:space-x-8">
            <div className="flex items-center space-x-2">
              <p className="text-xs font-medium text-muted-foreground">Rows per page</p>
              <Select
                value={String(rowsPerPage)}
                onValueChange={handlePageSizeChange}
              >
                <SelectTrigger className="h-8 w-[70px] text-xs">
                  <SelectValue placeholder={String(rowsPerPage)} />
                </SelectTrigger>
                <SelectContent side="top">
                  {[5, 10, 20, 30, 50].map((size) => (
                    <SelectItem key={size} value={String(size)} className="text-xs">
                      {size}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="flex w-[100px] items-center justify-center text-xs font-medium text-muted-foreground">
              Page {currentPage} of {totalPages}
            </div>

            <div className="flex items-center space-x-1">
              <Button
                variant="outline"
                className="hidden h-8 w-8 p-0 lg:flex"
                onClick={() => handlePageChange(1)}
                disabled={currentPage <= 1}
              >
                <span className="sr-only">Go to first page</span>
                <ChevronsLeft className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                className="h-8 w-8 p-0"
                onClick={() => handlePageChange(currentPage - 1)}
                disabled={currentPage <= 1}
              >
                <span className="sr-only">Go to previous page</span>
                <ChevronLeft className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                className="h-8 w-8 p-0"
                onClick={() => handlePageChange(currentPage + 1)}
                disabled={currentPage >= totalPages}
              >
                <span className="sr-only">Go to next page</span>
                <ChevronRight className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                className="hidden h-8 w-8 p-0 lg:flex"
                onClick={() => handlePageChange(totalPages)}
                disabled={currentPage >= totalPages}
              >
                <span className="sr-only">Go to last page</span>
                <ChevronsRight className="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}