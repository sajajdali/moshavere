import { useCallback, useEffect, useRef, useState } from 'react';

/**
 * اجرای یک درخواست async با مدیریت وضعیت بارگذاری و خطا.
 * درخواست های قدیمی هنگام تغییر ورودی لغو می شوند.
 */
export function useAsync(fn, deps = [], { immediate = true } = {}) {
  const [state, setState] = useState({ data: null, error: null, loading: immediate });
  const ctrlRef = useRef(null);
  const mounted = useRef(true);

  useEffect(() => () => { mounted.current = false; }, []);

  const run = useCallback(async () => {
    ctrlRef.current?.abort();
    const ctrl = new AbortController();
    ctrlRef.current = ctrl;

    setState((s) => ({ ...s, loading: true, error: null }));
    try {
      const data = await fn(ctrl.signal);
      if (!ctrl.signal.aborted && mounted.current) {
        setState({ data, error: null, loading: false });
      }
      return data;
    } catch (err) {
      if (err?.name === 'AbortError' || ctrl.signal.aborted || !mounted.current) return;
      setState({ data: null, error: err, loading: false });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);

  useEffect(() => {
    if (immediate) run();
    return () => ctrlRef.current?.abort();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [run, immediate]);

  return { ...state, reload: run };
}

export default useAsync;
