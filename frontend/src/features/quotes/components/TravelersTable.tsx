"use client";

import { useMemo, useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { Column } from "primereact/column";
import { DataTable } from "primereact/datatable";
import { Dialog } from "primereact/dialog";
import { Tag } from "primereact/tag";
import { FieldError, type FormLike } from "@/components/ui/FieldError";
import { useFormat } from "@/lib/hooks/useFormat";
import {
  emptyTraveler,
  TravelerModal,
  type TravelerInput,
} from "./TravelerModal";

interface TravelerRow extends TravelerInput {
  rowIndex: number;
}

interface TravelersTableProps {
  form: FormLike & {
    data: { travelers: TravelerInput[] };
    setData: (key: string, value: unknown) => unknown;
    validate: (name: string) => unknown;
    forgetError: (name: string) => unknown;
  };
  onChange?: () => void;
}

export type { TravelersTableProps };

export function TravelersTable({ form, onChange }: TravelersTableProps) {
  const t = useTranslations();
  const { formatDate } = useFormat();
  const [modalVisible, setModalVisible] = useState(false);
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<TravelerRow | null>(null);

  const rows = useMemo<TravelerRow[]>(
    () =>
      form.data.travelers.map((traveler, rowIndex) => ({
        ...traveler,
        rowIndex,
      })),
    [form.data.travelers],
  );

  const openAddModal = () => {
    setEditingIndex(null);
    setModalVisible(true);
  };

  const openEditModal = (rowIndex: number) => {
    setEditingIndex(rowIndex);
    setModalVisible(true);
  };

  const closeModal = () => {
    setModalVisible(false);
    setEditingIndex(null);
  };

  const validateTraveler = (index: number) => {
    form.validate(`travelers.${index}.name`);
    form.validate(`travelers.${index}.birth_date`);
  };

  const handleSave = (traveler: TravelerInput) => {
    onChange?.();

    if (editingIndex === null) {
      const newIndex = form.data.travelers.length;
      form.setData("travelers", [...form.data.travelers, traveler]);
      form.forgetError("travelers");
      validateTraveler(newIndex);
      form.validate("travelers");
    } else {
      const updated = [...form.data.travelers];
      updated[editingIndex] = traveler;
      form.setData("travelers", updated);
      form.forgetError("travelers");
      validateTraveler(editingIndex);
      form.validate("travelers");
    }

    closeModal();
  };

  const removeTraveler = (rowIndex: number) => {
    onChange?.();

    form.setData(
      "travelers",
      form.data.travelers.filter((_, index) => index !== rowIndex),
    );
    form.validate("travelers");
  };

  const openDeleteConfirm = (row: TravelerRow) => {
    setDeleteTarget(row);
  };

  const closeDeleteConfirm = () => {
    setDeleteTarget(null);
  };

  const confirmRemoveTraveler = () => {
    if (deleteTarget === null) {
      return;
    }

    removeTraveler(deleteTarget.rowIndex);
    closeDeleteConfirm();
  };

  const addOnsBody = (row: TravelerRow) => {
    if (row.add_ons.length === 0) {
      return <span className="text-slate-500">{t("summary.none")}</span>;
    }

    return (
      <div className="flex flex-wrap gap-1">
        {row.add_ons.map((addOn) => (
          <Tag key={addOn} value={t(`addOns.${addOn}`)} />
        ))}
      </div>
    );
  };

  const actionsBody = (row: TravelerRow) => (
    <div className="flex gap-1">
      <Button
        size="small"
        type="button"
        icon="pi pi-pencil"
        rounded
        text
        severity="secondary"
        aria-label={t("form.editTraveler")}
        onClick={() => openEditModal(row.rowIndex)}
      />
      <Button
        size="small"
        type="button"
        icon="pi pi-trash"
        rounded
        text
        severity="danger"
        aria-label={t("form.removeTraveler")}
        onClick={() => openDeleteConfirm(row)}
      />
    </div>
  );

  const header = (
    <div className="flex justify-between items-center">
      <span className="font-semibold">{t("form.travelers")}</span>
      <Button
        size="small"
        type="button"
        label={t("form.addTraveler")}
        icon="pi pi-user-plus"
        severity="secondary"
        outlined
        onClick={openAddModal}
      />
    </div>
  );

  const modalInitialValue = useMemo(() => {
    if (!modalVisible) {
      return emptyTraveler();
    }

    if (editingIndex !== null) {
      return form.data.travelers[editingIndex] ?? emptyTraveler();
    }

    return emptyTraveler();
  }, [modalVisible, editingIndex, form.data.travelers]);

  const deleteConfirmFooter = (
    <div className="flex justify-end gap-2">
      <Button
        size="small"
        type="button"
        label={t("form.cancel")}
        severity="secondary"
        outlined
        onClick={closeDeleteConfirm}
      />
      <Button
        size="small"
        type="button"
        label={t("form.removeTraveler")}
        icon="pi pi-trash"
        severity="danger"
        onClick={confirmRemoveTraveler}
      />
    </div>
  );

  return (
    <div className="mt-2">
      <DataTable
        value={rows}
        header={header}
        emptyMessage={t("form.emptyTravelers")}
        stripedRows
        dataKey="rowIndex"
        className="text-sm"
      >
        <Column field="name" header={t("form.travelerName")} sortable />
        <Column
          field="birth_date"
          header={t("form.birthDate")}
          body={(row: TravelerRow) => formatDate(row.birth_date)}
          sortable
        />
        <Column header={t("form.addOns")} body={addOnsBody} />
        <Column
          header={t("form.actions")}
          body={actionsBody}
          style={{ width: "6rem" }}
        />
      </DataTable>

      <FieldError form={form} name="travelers" />

      <TravelerModal
        visible={modalVisible}
        mode={editingIndex === null ? "add" : "edit"}
        initialValue={modalInitialValue ?? emptyTraveler()}
        onHide={closeModal}
        onSave={handleSave}
      />

      <Dialog
        visible={deleteTarget !== null}
        header={t("form.removeTravelerTitle")}
        modal
        draggable={false}
        className="w-full max-w-md"
        onHide={closeDeleteConfirm}
        footer={deleteConfirmFooter}
      >
        <p className="mt-2 text-slate-600 dark:text-zinc-300">
          {t("form.removeTravelerConfirm", {
            name: deleteTarget?.name ?? "",
          })}
        </p>
      </Dialog>
    </div>
  );
}
