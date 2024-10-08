import type { Plugin } from 'esbuild';
import TsPathsPlugin from './Plugin';

export function tsPathsPlugin(): Plugin {
  const { name, setup } = new TsPathsPlugin();

  return { name, setup };
}
