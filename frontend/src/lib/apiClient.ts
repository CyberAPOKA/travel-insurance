import axios, { AxiosError } from "axios";
import { API_BASE_URL } from "@/lib/constants";
import { localeRequestHeaders } from "@/lib/locale";

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
  timeout: 15000,
});

apiClient.interceptors.request.use((config) => {
  Object.assign(config.headers, localeRequestHeaders());

  return config;
});

export function setAuthToken(token: string | null) {
  if (token) {
    apiClient.defaults.headers.common.Authorization = `Bearer ${token}`;
    return;
  }

  delete apiClient.defaults.headers.common.Authorization;
}

export function extractApiErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const axiosError = error as AxiosError<{
      message?: string;
      errors?: Record<string, string[]>;
    }>;

    if (axiosError.code === "ERR_NETWORK") {
      return "Unable to reach the API. Make sure the backend is running at http://localhost:8000.";
    }

    if (axiosError.response?.status === 422 && axiosError.response.data.errors) {
      const messages = Object.values(axiosError.response.data.errors).flat();
      return messages.join(" ");
    }

    if (axiosError.response?.status === 401) {
      return "Your session has expired. Please sign in again.";
    }

    return axiosError.response?.data?.message ?? axiosError.message;
  }

  if (error instanceof Error) {
    return error.message;
  }

  return "Unknown error";
}
