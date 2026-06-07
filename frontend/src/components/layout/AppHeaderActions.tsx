"use client";

import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { Tag } from "primereact/tag";
import { useRouter } from "@/i18n/navigation";
import { logout } from "@/features/auth/services/authApi";
import { useAuthStore } from "@/features/auth/store/authStore";

export function AppHeaderActions({
  variant = "inline",
  onAction,
}: {
  variant?: "inline" | "stacked";
  onAction?: () => void;
}) {
  const t = useTranslations("auth");
  const router = useRouter();
  const user = useAuthStore((state) => state.user);
  const token = useAuthStore((state) => state.token);
  const clearSession = useAuthStore((state) => state.clearSession);

  const handleLogout = async () => {
    try {
      if (token) {
        await logout();
      }
    } finally {
      clearSession();
      onAction?.();
      router.replace("/");
    }
  };

  if (!token || !user) {
    return null;
  }

  if (variant === "stacked") {
    return (
      <div className="mt-auto flex flex-col gap-3 border-t border-slate-200 pt-4 dark:border-zinc-700">
        <Tag value={user.name} icon="pi pi-user" className="w-full justify-center" />
        <Button
          type="button"
          label={t("signOut")}
          icon="pi pi-sign-out"
          severity="secondary"
          outlined
          className="w-full"
          onClick={handleLogout}
        />
      </div>
    );
  }

  return (
    <div className="flex items-center gap-2">
      <Tag value={user.name} icon="pi pi-user" />
      <Button
        size="small"
        type="button"
        label={t("signOut")}
        icon="pi pi-sign-out"
        severity="secondary"
        outlined
        onClick={handleLogout}
      />
    </div>
  );
}
