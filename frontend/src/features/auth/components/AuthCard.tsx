"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { useForm } from "laravel-precognition-react";
import { Button } from "primereact/button";
import { Card } from "primereact/card";
import { FloatLabel } from "primereact/floatlabel";
import { InputText } from "primereact/inputtext";
import { Message } from "primereact/message";
import { Password } from "primereact/password";
import { FieldError, asFormLike, fieldClassName } from "@/components/ui/FieldError";
import { useAuthStore } from "@/features/auth/store/authStore";
import "@/lib/precognition";

type AuthMode = "login" | "register";

export function AuthCard() {
  const t = useTranslations("auth");
  const setSession = useAuthStore((state) => state.setSession);
  const [mode, setMode] = useState<AuthMode>("login");
  const [submitError, setSubmitError] = useState<string | null>(null);

  const loginForm = useForm("post", "/auth/login", {
    email: "",
    password: "",
  });

  const registerForm = useForm("post", "/auth/register", {
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
  });

  loginForm.setValidationTimeout(500);
  registerForm.setValidationTimeout(500);

  const form = mode === "login" ? loginForm : registerForm;

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSubmitError(null);

    try {
      const response = await form.submit();
      const payload = (response as { data?: { user: { id: number; name: string; email: string }; token: string } } | null)?.data;

      if (!payload?.user || !payload?.token) {
        setSubmitError(t("unexpectedError"));
        return;
      }

      setSession(payload.user, payload.token);
    } catch {
      if (!form.hasErrors) {
        setSubmitError(t("unexpectedError"));
      }
    }
  };

  return (
    <div className="flex min-h-[70vh] items-center justify-center py-8">
      <Card className="w-full max-w-md shadow-md">
        <div className="mb-6 grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 dark:bg-zinc-800">
          <Button
            type="button"
            label={t("signIn")}
            severity={mode === "login" ? "contrast" : "secondary"}
            text={mode !== "login"}
            className="w-full !justify-center"
            onClick={() => {
              setMode("login");
              setSubmitError(null);
            }}
          />
          <Button
            type="button"
            label={t("registerTab")}
            severity={mode === "register" ? "contrast" : "secondary"}
            text={mode !== "register"}
            className="w-full !justify-center"
            onClick={() => {
              setMode("register");
              setSubmitError(null);
            }}
          />
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {mode === "register" ? (
            <div>
              <FloatLabel>
                <InputText
                  id="register-name"
                  value={registerForm.data.name}
                  className={fieldClassName(asFormLike(registerForm), "name")}
                  onChange={(event) => registerForm.setData("name", event.target.value)}
                  onBlur={() => registerForm.validate("name")}
                />
                <label htmlFor="register-name">{t("fullName")}</label>
              </FloatLabel>
              <FieldError form={asFormLike(registerForm)} name="name" />
            </div>
          ) : null}

          <div>
            <FloatLabel>
              <InputText
                id="auth-email"
                type="email"
                value={form.data.email}
                className={fieldClassName(asFormLike(form), "email")}
                onChange={(event) => form.setData("email", event.target.value)}
                onBlur={() => form.validate("email")}
              />
              <label htmlFor="auth-email">{t("email")}</label>
            </FloatLabel>
            <FieldError form={asFormLike(form)} name="email" />
          </div>

          <div>
            <FloatLabel>
              <Password
                inputId="auth-password"
                value={form.data.password}
                onChange={(event) => form.setData("password", event.target.value)}
                onBlur={() => form.validate("password")}
                feedback={mode === "register"}
                toggleMask
                className="!w-full"
                inputClassName={fieldClassName(asFormLike(form), "password", "w-full")}
              />
              <label htmlFor="auth-password">{t("password")}</label>
            </FloatLabel>
            {mode === "register" ? (
              <p className="mt-1 text-xs text-slate-500 dark:text-zinc-400">{t("passwordHint")}</p>
            ) : null}
            <FieldError form={asFormLike(form)} name="password" />
          </div>

          {mode === "register" ? (
            <div>
              <FloatLabel>
                <Password
                  inputId="auth-password-confirmation"
                  value={registerForm.data.password_confirmation}
                  onChange={(event) =>
                    registerForm.setData("password_confirmation", event.target.value)
                  }
                  onBlur={() => registerForm.validate("password_confirmation")}
                  feedback={false}
                  toggleMask
                  className="w-full"
                  inputClassName={fieldClassName(
                    asFormLike(registerForm),
                    "password_confirmation",
                    "w-full",
                  )}
                />
                <label htmlFor="auth-password-confirmation">{t("passwordConfirmation")}</label>
              </FloatLabel>
              <FieldError form={asFormLike(registerForm)} name="password_confirmation" />
            </div>
          ) : null}

          {submitError ? <Message severity="error" text={submitError} className="w-full" /> : null}

          <Button
            type="submit"
            label={
              form.processing
                ? mode === "login"
                  ? t("signingIn")
                  : t("creatingAccount")
                : mode === "login"
                  ? t("signIn")
                  : t("createAccount")
            }
            outlined={mode === "login"}
            className="w-full"
            disabled={form.processing}
          />
        </form>
      </Card>
    </div>
  );
}