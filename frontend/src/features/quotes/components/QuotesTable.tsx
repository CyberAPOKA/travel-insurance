"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { Card } from "primereact/card";
import { Column } from "primereact/column";
import {
  DataTable,
  type DataTableFilterEvent,
  type DataTablePageEvent,
  type DataTableProps,
} from "primereact/datatable";
import { IconField } from "primereact/iconfield";
import { InputIcon } from "primereact/inputicon";
import { InputText } from "primereact/inputtext";
import { Message } from "primereact/message";
import { Tag } from "primereact/tag";
import { useRouter } from "@/i18n/navigation";
import {
  DateFilterInput,
  DropdownFilterInput,
  FilterApplyButton,
  FilterClearButton,
  NumericFilterInput,
  type ColumnFilterOptions,
  type DataTableFiltersState,
} from "@/lib/filters";
import { useFormat } from "@/lib/hooks/useFormat";
import { DESTINATION_ZONES } from "@/lib/constants";
import {
  createQuoteTableFilters,
  QUOTE_TABLE_GLOBAL_FILTER_FIELDS,
} from "../filters/quoteTableFilters";
import { extractApiErrorMessage, fetchQuotes } from "../services/quoteApi";
import type { DestinationZone, QuoteListItem } from "../types/quote";

const FILTER_DEBOUNCE_MS = 350;

type PrimeDataTableFilters = NonNullable<
  DataTableProps<QuoteListItem[]>["filters"]
>;

export function QuotesTable() {
  const t = useTranslations("quotesList");
  const tRoot = useTranslations();
  const { formatCurrency, formatDate, intlLocale, calendarDateFormat } =
    useFormat();
  const router = useRouter();
  const [quotes, setQuotes] = useState<QuoteListItem[]>([]);
  const [filters, setFilters] = useState<DataTableFiltersState>(() =>
    createQuoteTableFilters(),
  );
  const [debouncedFilters, setDebouncedFilters] =
    useState<DataTableFiltersState>(() => createQuoteTableFilters());
  const [globalFilterValue, setGlobalFilterValue] = useState("");
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(10);
  const [totalRecords, setTotalRecords] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      setDebouncedFilters(filters);
    }, FILTER_DEBOUNCE_MS);

    return () => window.clearTimeout(timeoutId);
  }, [filters]);

  useEffect(() => {
    setPage(1);
  }, [debouncedFilters]);

  const loadQuotes = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const result = await fetchQuotes(debouncedFilters, {
        page,
        per_page: perPage,
      });
      setQuotes(result.data);
      setTotalRecords(result.meta.total);
    } catch (loadError) {
      setError(extractApiErrorMessage(loadError) || t("loadError"));
    } finally {
      setLoading(false);
    }
  }, [debouncedFilters, page, perPage, t]);

  useEffect(() => {
    void loadQuotes();
  }, [loadQuotes]);

  const destinationOptions = useMemo(
    () =>
      DESTINATION_ZONES.map((zone) => ({
        label: tRoot(`destinations.${zone}`),
        value: zone,
      })),
    [tRoot],
  );

  const clearFilters = () => {
    setFilters(createQuoteTableFilters());
    setGlobalFilterValue("");
  };

  const onGlobalFilterChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const value = event.target.value;

    setGlobalFilterValue(value);
    setFilters((current: DataTableFiltersState) => ({
      ...current,
      global: {
        ...(current.global as { value: string | null; matchMode: string }),
        value: value || null,
      },
    }));
  };

  const onFilter = (event: DataTableFilterEvent) => {
    setFilters(event.filters as DataTableFiltersState);
  };

  const onPageChange = (event: DataTablePageEvent) => {
    setPage((event.page ?? 0) + 1);
    setPerPage(event.rows);
  };

  const destinationBody = (row: QuoteListItem) => {
    const label = DESTINATION_ZONES.includes(row.destination as DestinationZone)
      ? tRoot(`destinations.${row.destination as DestinationZone}`)
      : row.destination;

    return <Tag value={label} />;
  };

  const currencyBody = (field: "final_total") => (row: QuoteListItem) =>
    formatCurrency(row[field]);

  const dateBody = (field: "start_date" | "end_date") => (row: QuoteListItem) =>
    formatDate(row[field]);

  const discountBody = (row: QuoteListItem) =>
    row.group_discount_percentage > 0
      ? `${row.group_discount_percentage}%`
      : "-";

  const viewQuoteBody = (row: QuoteListItem) => (
    <Button
      size="small"
      type="button"
      icon="pi pi-external-link"
      text
      aria-label={t("viewQuote")}
      onClick={() => router.push(`/quotes/${row.id}`)}
    />
  );

  const destinationFilterTemplate = (options: ColumnFilterOptions) => (
    <DropdownFilterInput
      options={options}
      items={destinationOptions}
      placeholder={t("filterDestination")}
    />
  );

  const dateFilterTemplate = (options: ColumnFilterOptions) => (
    <DateFilterInput
      options={options}
      placeholder={t("filterDate")}
      dateFormat={calendarDateFormat}
    />
  );

  const chargedDaysFilterTemplate = (options: ColumnFilterOptions) => (
    <NumericFilterInput
      options={options}
      placeholder={t("filterDays")}
      locale={intlLocale}
    />
  );

  const travelersFilterTemplate = (options: ColumnFilterOptions) => (
    <NumericFilterInput
      options={options}
      placeholder={t("filterTravelers")}
      locale={intlLocale}
    />
  );

  const discountFilterTemplate = (options: ColumnFilterOptions) => (
    <NumericFilterInput
      options={options}
      placeholder={t("filterDiscount")}
      locale={intlLocale}
      suffix="%"
      min={0}
      max={100}
    />
  );

  const totalFilterTemplate = (options: ColumnFilterOptions) => (
    <NumericFilterInput
      options={options}
      placeholder={t("filterTotal")}
      locale={intlLocale}
      mode="currency"
      currency="BRL"
    />
  );

  const header = (
    <div className="flex flex-col justify-between gap-2 md:flex-row md:items-center">
      <IconField iconPosition="left" className="w-full md:w-72">
        <InputIcon className="pi pi-search" />
        <InputText
          value={globalFilterValue}
          onChange={onGlobalFilterChange}
          placeholder={t("globalSearch")}
        />
      </IconField>
      <div className="flex gap-2">
        <Button
          type="button"
          label={t("clearFilters")}
          icon="pi pi-filter-slash"
          severity="secondary"
          outlined
          onClick={clearFilters}
        />
        <Button
          type="button"
          label={t("newQuote")}
          icon="pi pi-plus"
          onClick={() => router.push("/quotes/new")}
        />
      </div>
    </div>
  );

  return (
    <Card title={t("title")} subTitle={t("subtitle")} className="shadow-md">
      {error ? (
        <Message severity="error" text={error} className="mb-3 w-full" />
      ) : null}

      <div className="overflow-x-auto">
        <DataTable
          value={quotes}
          loading={loading}
          lazy
          paginator
          first={(page - 1) * perPage}
          rows={perPage}
          totalRecords={totalRecords}
          rowsPerPageOptions={[5, 10, 25, 50]}
          onPage={onPageChange}
          emptyMessage={t("empty")}
          stripedRows
          removableSort
          header={header}
          dataKey="id"
          filters={filters as PrimeDataTableFilters}
          globalFilterFields={QUOTE_TABLE_GLOBAL_FILTER_FIELDS}
          onFilter={onFilter}
          className="quotes-table min-w-[80rem] text-sm"
          tableStyle={{ minWidth: "80rem" }}
        >
          <Column
            field="destination"
            header={t("destination")}
            body={destinationBody}
            filter
            showFilterMatchModes={false}
            filterElement={destinationFilterTemplate}
            sortable
            style={{ minWidth: "10rem" }}
          />
          <Column
            field="start_date"
            header={t("startDate")}
            body={dateBody("start_date")}
            dataType="date"
            filter
            filterElement={dateFilterTemplate}
            filterClear={FilterClearButton}
            filterApply={FilterApplyButton}
            sortable
            style={{ minWidth: "8.5rem" }}
          />
          <Column
            field="end_date"
            header={t("endDate")}
            body={dateBody("end_date")}
            dataType="date"
            filter
            filterElement={dateFilterTemplate}
            filterClear={FilterClearButton}
            filterApply={FilterApplyButton}
            sortable
            style={{ minWidth: "8.5rem" }}
          />
          <Column
            field="charged_days"
            header={t("chargedDays")}
            dataType="numeric"
            filter
            filterElement={chargedDaysFilterTemplate}
            filterClear={FilterClearButton}
            filterApply={FilterApplyButton}
            sortable
            style={{ minWidth: "7.5rem" }}
          />
          <Column
            field="travelers_count"
            header={t("travelers")}
            dataType="numeric"
            filter
            filterElement={travelersFilterTemplate}
            filterClear={FilterClearButton}
            filterApply={FilterApplyButton}
            sortable
            style={{ minWidth: "6.5rem" }}
          />
          <Column
            field="group_discount_percentage"
            header={t("discount")}
            body={discountBody}
            dataType="numeric"
            filter
            filterElement={discountFilterTemplate}
            filterClear={FilterClearButton}
            filterApply={FilterApplyButton}
            sortable
            style={{ minWidth: "6rem" }}
          />
          <Column
            field="final_total"
            header={t("total")}
            body={currencyBody("final_total")}
            dataType="numeric"
            filter
            filterElement={totalFilterTemplate}
            filterClear={FilterClearButton}
            filterApply={FilterApplyButton}
            sortable
            style={{ minWidth: "8rem" }}
          />
          <Column
            header={t("actions")}
            body={viewQuoteBody}
            style={{ minWidth: "4rem" }}
          />
        </DataTable>
      </div>
    </Card>
  );
}
