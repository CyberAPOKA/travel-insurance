import { client } from "laravel-precognition-react";
import { API_BASE_URL } from "@/lib/constants";
import { localeRequestHeaders } from "@/lib/locale";
import { useAuthStore } from "@/features/auth/store/authStore";

type ClientMethod = "get" | "post" | "put" | "patch" | "delete";

function withAuthHeaders<T extends Record<string, unknown>>(config: T = {} as T) {
  const token = useAuthStore.getState().token;

  return {
    ...config,
    headers: {
      ...(config.headers as Record<string, string> | undefined),
      ...localeRequestHeaders(),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  };
}

function wrapClientMethod(method: ClientMethod) {
  const original = client[method].bind(client);

  client[method] = ((url: string, data?: Record<string, unknown>, config?: Record<string, unknown>) =>
    original(url, data, withAuthHeaders(config))) as typeof client[typeof method];
}

let isConfigured = false;

export function setupPrecognitionClient() {
  if (isConfigured) {
    return;
  }

  client.withBaseURL(API_BASE_URL);

  (["get", "post", "put", "patch", "delete"] as ClientMethod[]).forEach(wrapClientMethod);

  isConfigured = true;
}

setupPrecognitionClient();
