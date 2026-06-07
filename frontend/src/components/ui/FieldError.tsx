"use client";

type FormLike = {
  invalid: (name: string) => boolean;
  errors: Record<string, string | string[]>;
};

export type { FormLike };

interface FieldErrorProps {
  form: FormLike;
  name: string;
}

export function FieldError({ form, name }: FieldErrorProps) {
  if (!form.invalid(name)) {
    return null;
  }

  const error = form.errors[name];
  const message = Array.isArray(error) ? error[0] : error;

  return (
    <small className="mt-1 flex items-center gap-1 text-red-500">
      <i className="pi pi-exclamation-circle text-xs" />
      {message}
    </small>
  );
}

export function fieldClassName(form: FormLike, name: string, className = "w-full") {
  return `${className}${form.invalid(name) ? " p-invalid" : ""}`;
}

export function asFormLike<T extends { invalid: (name: never) => boolean; errors: object }>(
  form: T,
): FormLike {
  return form as unknown as FormLike;
}
